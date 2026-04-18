<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * Guarded attributes
     */
    protected $guarded = ['id'];

    /**
     * Casts
     */
    protected $casts = [
        'placed_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'is_future_order' => 'boolean',
        'total_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'verification_status' => 'string',
    ];

    /* -------------------------------------------------------------------------- */
    /* Boot / Order Number                                                        */
    /* -------------------------------------------------------------------------- */

    protected static function booted()
    {
        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber();
            }
        });

        static::created(function (Order $order) {
            if ($order->billing_address_id && $billing = $order->billingAddress) {
                $order->addresses()->create([
                    'type' => 'billing',
                    'name' => $billing->contact_name ?: ($order->customer->name ?? 'N/A'),
                    'address_line_1' => $billing->address_line1,
                    'address_line_2' => $billing->address_line2,
                    'city' => $billing->district ?? $billing->village ?? $billing->taluka ?? 'N/A',
                    'state' => $billing->state,
                    'postal_code' => $billing->pincode,
                    'country' => $billing->country ?: 'India',
                    'phone' => $billing->contact_phone ?: ($order->customer->phone ?? null),
                ]);
            }

            if ($order->shipping_address_id && $shipping = $order->shippingAddress) {
                $order->addresses()->create([
                    'type' => 'shipping',
                    'name' => $shipping->contact_name ?: ($order->customer->name ?? 'N/A'),
                    'address_line_1' => $shipping->address_line1,
                    'address_line_2' => $shipping->address_line2,
                    'city' => $shipping->district ?? $shipping->village ?? $shipping->taluka ?? 'N/A',
                    'state' => $shipping->state,
                    'postal_code' => $shipping->pincode,
                    'country' => $shipping->country ?: 'India',
                    'phone' => $shipping->contact_phone ?: ($order->customer->phone ?? null),
                ]);
            }
        });

        static::updated(function (Order $order) {
            if ($order->wasChanged('billing_address_id') && $order->billing_address_id && $billing = $order->billingAddress) {
                $order->addresses()->updateOrCreate(
                    ['type' => 'billing'],
                    [
                        'name' => $billing->contact_name ?: ($order->customer->name ?? 'N/A'),
                        'address_line_1' => $billing->address_line1,
                        'address_line_2' => $billing->address_line2,
                        'city' => $billing->district ?? $billing->village ?? $billing->taluka ?? 'N/A',
                        'state' => $billing->state,
                        'postal_code' => $billing->pincode,
                        'country' => $billing->country ?: 'India',
                        'phone' => $billing->contact_phone ?: ($order->customer->phone ?? null),
                    ]
                );
            }

            if ($order->wasChanged('shipping_address_id') && $order->shipping_address_id && $shipping = $order->shippingAddress) {
                $order->addresses()->updateOrCreate(
                    ['type' => 'shipping'],
                    [
                        'name' => $shipping->contact_name ?: ($order->customer->name ?? 'N/A'),
                        'address_line_1' => $shipping->address_line1,
                        'address_line_2' => $shipping->address_line2,
                        'city' => $shipping->district ?? $shipping->village ?? $shipping->taluka ?? 'N/A',
                        'state' => $shipping->state,
                        'postal_code' => $shipping->pincode,
                        'country' => $shipping->country ?: 'India',
                        'phone' => $shipping->contact_phone ?: ($order->customer->phone ?? null),
                    ]
                );
            }
        });
    }

    /**
     * Order number format:
     * ORD-YYYYMMDD-XXXX
     */
    public static function generateOrderNumber(): string
    {
        $date = now()->format('Ymd');

        return DB::transaction(function () use ($date) {

            $lastOrder = self::whereDate('created_at', now())
                ->lockForUpdate()
                ->latest('id')
                ->first();

            $lastSequence = 0;

            if (
                $lastOrder &&
                preg_match('/ORD-\d{8}-(\d+)/', $lastOrder->order_number, $matches)
            ) {
                $lastSequence = (int) $matches[1];
            }

            $nextSequence = str_pad((string) ($lastSequence + 1), 4, '0', STR_PAD_LEFT);

            return "ORD-{$date}-{$nextSequence}";
        });
    }

    /* -------------------------------------------------------------------------- */
    /* Activity Log                                                               */
    /* -------------------------------------------------------------------------- */

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'payment_status', 'shipping_status'])
            ->logOnlyDirty();
    }

    /* -------------------------------------------------------------------------- */
    /* Relationships                                                             */
    /* -------------------------------------------------------------------------- */

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(OrderAddress::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(OrderVerification::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(OrderReturn::class);
    }

    public function trackings(): HasMany
    {
        return $this->hasMany(OrderTracking::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    /* -------------------------------------------------------------------------- */
    /* Scopes                                                                     */
    /* -------------------------------------------------------------------------- */

    public function scopeUnverified($query)
    {
        return $query->whereIn('verification_status', ['unverified', 'pending_followup']);
    }

    /**
     * Billing address snapshot (customer address at order time)
     */
    public function billingAddress(): BelongsTo
    {
        return $this->belongsTo(CustomerAddress::class, 'billing_address_id');
    }

    /**
     * Shipping address snapshot (customer address at order time)
     */
    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(CustomerAddress::class, 'shipping_address_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /* -------------------------------------------------------------------------- */
    /* Helpers (VERY IMPORTANT for Print Invoice)                                 */
    /* -------------------------------------------------------------------------- */

    /**
     * Get the total paid amount dynamically (from invoices or sum column).
     */
    public function getPaidAmountAttribute()
    {
        if (isset($this->attributes['paid_amount'])) {
            return $this->attributes['paid_amount'];
        }

        if (array_key_exists('invoices_sum_paid_amount', $this->attributes)) {
            return (float) $this->attributes['invoices_sum_paid_amount'];
        }

        if ($this->relationLoaded('invoices')) {
            return (float) $this->invoices->sum('paid_amount');
        }

        return (float) $this->invoices()->sum('paid_amount');
    }

    /**
     * Get latest invoice safely
     */
    public function latestInvoice(): ?Invoice
    {
        return $this->invoices()->latest()->first();
    }

    /**
     * Check if order has any invoice
     */
    public function hasInvoice(): bool
    {
        return $this->invoices()->exists();
    }
}
