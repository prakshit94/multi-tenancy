@extends('layouts.app')

@section('content')
<div class="flex flex-1 flex-col space-y-8 p-6 md:p-8 max-w-7xl mx-auto w-full animate-in fade-in duration-500">
    
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <a href="{{ $indexUrl }}" class="group flex h-10 w-10 items-center justify-center rounded-xl bg-background border border-border/50 shadow-sm transition-all hover:bg-accent hover:text-accent-foreground hover:scale-105 active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground group-hover:text-foreground transition-colors"><path d="m15 18-6-6 6-6"/></svg>
            </a>
            <div class="space-y-1">
                <h1 class="text-3xl font-bold tracking-tight bg-gradient-to-r from-foreground to-foreground/70 bg-clip-text text-transparent">
                    {{ isset($user) ? 'Edit User' : 'Create New User' }}
                </h1>
                <p class="text-muted-foreground text-sm">
                    {{ isset($user) ? 'Update user details, roles, and permissions.' : 'Onboard a new member to your organization.' }}
                </p>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="rounded-2xl border border-border/40 bg-card/50 backdrop-blur-xl shadow-lg shadow-black/5 overflow-hidden">
        
        <!-- Decoration Line -->
        <div class="h-1 w-full bg-gradient-to-r from-primary/20 via-primary/50 to-primary/20"></div>

        <form action="{{ $actionUrl }}" method="POST" class="p-6 md:p-8 space-y-8"
              x-data='{ 
                  showPassword: false, 
                  showConfirm: false,
                  selectedRoles: @json(old("roles", isset($user) ? $user->roles->pluck("name")->toArray() : [])),
                  addressSuggestions: [],
                  activeLookupField: "",
                  first_name: "{{ old("first_name", $user->first_name ?? "") }}",
                  middle_name: "{{ old("middle_name", $user->middle_name ?? "") }}",
                  last_name: "{{ old("last_name", $user->last_name ?? "") }}",
                  village: "{{ old("village", $user->village ?? "") }}",
                  pincode: "{{ old("pincode", $user->pincode ?? "") }}",
                  post_office: "{{ old("post_office", $user->post_office ?? "") }}",
                  taluka: "{{ old("taluka", $user->taluka ?? "") }}",
                  district: "{{ old("district", $user->district ?? "") }}",
                  state: "{{ old("state", $user->state ?? "") }}",
                  address: "{{ old("address", $user->address ?? "") }}",

                  async lookupAddress(field, value) {
                      if (!value || value.toString().trim().length < 2) {
                          this.addressSuggestions = [];
                          return;
                      }
                      if (field === "pincode" && value.toString().length < 6) return;
                      try {
                          const encodedValue = encodeURIComponent(value);
                          let res = await fetch(`{{ url("/api/village-lookup") }}?${field}=${encodedValue}`, {
                              headers: { "Accept": "application/json", "X-Requested-With": "XMLHttpRequest" }
                          });
                          if (!res.ok) throw new Error("Lookup failed");
                          let data = await res.json();
                          if (data.found) {
                              if (data.mode === "single") {
                                  this.fillAddress(data.data);
                                  this.addressSuggestions = [];
                              } else {
                                  this.addressSuggestions = data.list;
                              }
                          } else { this.addressSuggestions = []; }
                      } catch (e) { console.error("Lookup error:", e); this.addressSuggestions = []; }
                  },
                  lookupAddressOnFocus(field) {
                      if (this[field] && this[field].toString().trim() !== "") return;
                      const baseValue = this.pincode || this.village || "";
                      if (baseValue.length >= 2) { this.lookupAddress(field, baseValue); }
                  },
                  fillAddress(data) {
                      if (data.village) this.village = data.village;
                      if (data.pincode) this.pincode = data.pincode;
                      if (data.taluka) this.taluka = data.taluka;
                      if (data.district) this.district = data.district;
                      if (data.state) this.state = data.state;
                      if (data.post_office) this.post_office = data.post_office;
                      this.addressSuggestions = [];
                  }
              }'>
            @csrf
            @if(isset($user))
                @method('PUT')
            @endif

            <div class="grid gap-8 lg:grid-cols-3">
                
                <!-- Account Details Column -->
                <div class="lg:col-span-2 space-y-6">
                    <div>
                        <h2 class="text-lg font-semibold tracking-tight">Account Information</h2>
                        <p class="text-sm text-muted-foreground">Basic identity details for login and display.</p>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-3">
                        <!-- First Name -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium leading-none text-foreground/80" for="first_name">
                                First Name <span class="text-destructive">*</span>
                            </label>
                            <div class="relative group">
                                <span class="absolute left-3 top-2.5 text-muted-foreground group-focus-within:text-primary transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                </span>
                                <input class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 pl-10 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm" 
                                       id="first_name" name="first_name" x-model="first_name" placeholder="John" required>
                            </div>
                            @error('first_name') <p class="text-[0.8rem] font-medium text-destructive mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Middle Name -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium leading-none text-foreground/80" for="middle_name">
                                Middle Name
                            </label>
                            <div class="relative group">
                                <span class="absolute left-3 top-2.5 text-muted-foreground group-focus-within:text-primary transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                </span>
                                <input class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 pl-10 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm" 
                                       id="middle_name" name="middle_name" x-model="middle_name" placeholder="B.">
                            </div>
                            @error('middle_name') <p class="text-[0.8rem] font-medium text-destructive mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Last Name -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium leading-none text-foreground/80" for="last_name">
                                Last Name
                            </label>
                            <div class="relative group">
                                <span class="absolute left-3 top-2.5 text-muted-foreground group-focus-within:text-primary transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                </span>
                                <input class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 pl-10 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm" 
                                       id="last_name" name="last_name" x-model="last_name" placeholder="Doe">
                            </div>
                            @error('last_name') <p class="text-[0.8rem] font-medium text-destructive mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2 mt-6">
                        
                        <!-- Email -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium leading-none text-foreground/80" for="email">
                                Email Address <span class="text-destructive">*</span>
                            </label>
                            <div class="relative group">
                                <span class="absolute left-3 top-2.5 text-muted-foreground group-focus-within:text-primary transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                                </span>
                                <input class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 pl-10 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm" 
                                       id="email" name="email" type="email" placeholder="name@company.com" required 
                                       value="{{ old('email', $user->email ?? '') }}">
                            </div>
                            @error('email') <p class="text-[0.8rem] font-medium text-destructive mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Phone Number -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium leading-none text-foreground/80" for="phone">
                                Phone Number
                            </label>
                            <div class="relative group">
                                <span class="absolute left-3 top-2.5 text-muted-foreground group-focus-within:text-primary transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l2.27-2.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                </span>
                                <input class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 pl-10 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm" 
                                       id="phone" name="phone" placeholder="+1 (555) 000-0000" 
                                       value="{{ old('phone', $user->phone ?? '') }}">
                            </div>
                            @error('phone') <p class="text-[0.8rem] font-medium text-destructive mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Designation -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium leading-none text-foreground/80" for="designation">
                                Designation
                            </label>
                            <div class="relative group">
                                <span class="absolute left-3 top-2.5 text-muted-foreground group-focus-within:text-primary transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                                </span>
                                <input class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 pl-10 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm" 
                                       id="designation" name="designation" placeholder="Manager / Supervisor" 
                                       value="{{ old('designation', $user->designation ?? '') }}">
                            </div>
                            @error('designation') <p class="text-[0.8rem] font-medium text-destructive mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Department -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium leading-none text-foreground/80" for="department">
                                Department
                            </label>
                            <div class="relative group">
                                <span class="absolute left-3 top-2.5 text-muted-foreground group-focus-within:text-primary transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5a2.5 2.5 0 0 1 2.5-2.5h11"/></svg>
                                </span>
                                <input class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 pl-10 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm" 
                                       id="department" name="department" placeholder="E.g. Engineering, HR" 
                                       value="{{ old('department', $user->department ?? '') }}">
                            </div>
                            @error('department') <p class="text-[0.8rem] font-medium text-destructive mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Employee ID -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium leading-none text-foreground/80" for="employee_id">
                                Employee ID
                            </label>
                            <div class="relative group">
                                <span class="absolute left-3 top-2.5 text-muted-foreground group-focus-within:text-primary transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><path d="M7 7h10"/><path d="M7 11h10"/><path d="M7 15h6"/></svg>
                                </span>
                                <input class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 pl-10 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm" 
                                       id="employee_id" name="employee_id" placeholder="EMP-001" 
                                       value="{{ old('employee_id', $user->employee_id ?? '') }}">
                            </div>
                            @error('employee_id') <p class="text-[0.8rem] font-medium text-destructive mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Gender -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium leading-none text-foreground/80" for="gender">
                                Gender
                            </label>
                            <div class="relative group">
                                <span class="absolute left-3 top-2.5 text-muted-foreground group-focus-within:text-primary transition-colors z-10 pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="10" r="4"/><path d="M12 14v7"/><path d="M9 18h6"/></svg>
                                </span>
                                <select class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 pl-10 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm"
                                        id="gender" name="gender">
                                    <option value="">Select Gender</option>
                                    <option value="Male" {{ old('gender', $user->gender ?? '') == 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ old('gender', $user->gender ?? '') == 'Female' ? 'selected' : '' }}>Female</option>
                                    <option value="Other" {{ old('gender', $user->gender ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            @error('gender') <p class="text-[0.8rem] font-medium text-destructive mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Date of Birth -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium leading-none text-foreground/80" for="date_of_birth">
                                Date of Birth
                            </label>
                            <div class="relative group">
                                <span class="absolute left-3 top-2.5 text-muted-foreground group-focus-within:text-primary transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                                </span>
                                <input class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 pl-10 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm" 
                                       id="date_of_birth" name="date_of_birth" type="date" 
                                       value="{{ old('date_of_birth', isset($user) && $user->date_of_birth ? $user->date_of_birth->format('Y-m-d') : '') }}">
                            </div>
                            @error('date_of_birth') <p class="text-[0.8rem] font-medium text-destructive mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Joining Date -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium leading-none text-foreground/80" for="joining_date">
                                Joining Date
                            </label>
                            <div class="relative group">
                                <span class="absolute left-3 top-2.5 text-muted-foreground group-focus-within:text-primary transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/><path d="m9 16 2 2 4-4"/></svg>
                                </span>
                                <input class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 pl-10 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm" 
                                       id="joining_date" name="joining_date" type="date" 
                                       value="{{ old('joining_date', isset($user) && $user->joining_date ? $user->joining_date->format('Y-m-d') : '') }}">
                            </div>
                            @error('joining_date') <p class="text-[0.8rem] font-medium text-destructive mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Address Information -->
                        <div class="space-y-4 sm:col-span-2 p-4 rounded-xl bg-muted/20 border border-border/30">
                            <div class="text-xs font-bold uppercase tracking-wider text-muted-foreground">
                                Address Information
                            </div>

                            <div class="grid md:grid-cols-2 gap-4">
                                <!-- Village -->
                                <div class="relative space-y-2">
                                    <label class="text-sm font-medium leading-none text-foreground/80" for="village">Village</label>
                                    <input class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30" 
                                           id="village" name="village" x-model="village"
                                           @focus="activeLookupField = 'village'; lookupAddressOnFocus('village')"
                                           @input.debounce.300ms="activeLookupField = 'village'; lookupAddress('village', $el.value)"
                                           placeholder="Enter Village Name">
                                    
                                    <!-- Suggestions Dropdown -->
                                    <div x-show="addressSuggestions.length && activeLookupField === 'village'"
                                         class="absolute z-50 w-full mt-1 rounded-xl border border-border bg-background shadow-xl max-h-48 overflow-auto animate-in fade-in zoom-in-95 duration-200">
                                        <template x-for="item in addressSuggestions">
                                            <div @click="fillAddress(item.data)"
                                                 class="px-4 py-2.5 text-sm hover:bg-primary/10 hover:text-primary cursor-pointer transition-colors border-b last:border-0 border-border/50"
                                                 x-text="item.label"></div>
                                        </template>
                                    </div>
                                    @error('village') <p class="text-[0.8rem] font-medium text-destructive mt-1">{{ $message }}</p> @enderror
                                </div>

                                <!-- Pincode -->
                                <div class="relative space-y-2">
                                    <label class="text-sm font-medium leading-none text-foreground/80" for="pincode">Pincode</label>
                                    <input class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30" 
                                           id="pincode" name="pincode" x-model="pincode" maxlength="6"
                                           @focus="activeLookupField = 'pincode'; lookupAddressOnFocus('pincode')"
                                           @input.debounce.300ms="activeLookupField = 'pincode'; lookupAddress('pincode', $el.value)"
                                           placeholder="6-digit Pincode">
                                    
                                    <!-- Suggestions Dropdown -->
                                    <div x-show="addressSuggestions.length && activeLookupField === 'pincode'"
                                         class="absolute z-50 w-full mt-1 rounded-xl border border-border bg-background shadow-xl max-h-48 overflow-auto animate-in fade-in zoom-in-95 duration-200">
                                        <template x-for="item in addressSuggestions">
                                            <div @click="fillAddress(item.data)"
                                                 class="px-4 py-2.5 text-sm hover:bg-primary/10 hover:text-primary cursor-pointer transition-colors border-b last:border-0 border-border/50"
                                                 x-text="item.label"></div>
                                        </template>
                                    </div>
                                    @error('pincode') <p class="text-[0.8rem] font-medium text-destructive mt-1">{{ $message }}</p> @enderror
                                </div>

                                <!-- Post Office -->
                                <div class="space-y-2">
                                    <label class="text-sm font-medium leading-none text-foreground/80" for="post_office">Post Office</label>
                                    <input class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30" 
                                           id="post_office" name="post_office" x-model="post_office" placeholder="Post Office">
                                    @error('post_office') <p class="text-[0.8rem] font-medium text-destructive mt-1">{{ $message }}</p> @enderror
                                </div>

                                <!-- Taluka -->
                                <div class="space-y-2">
                                    <label class="text-sm font-medium leading-none text-foreground/80" for="taluka">Taluka</label>
                                    <input class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30" 
                                           id="taluka" name="taluka" x-model="taluka" placeholder="Taluka">
                                    @error('taluka') <p class="text-[0.8rem] font-medium text-destructive mt-1">{{ $message }}</p> @enderror
                                </div>

                                <!-- District -->
                                <div class="space-y-2">
                                    <label class="text-sm font-medium leading-none text-foreground/80" for="district">District</label>
                                    <input class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30" 
                                           id="district" name="district" x-model="district" placeholder="District">
                                    @error('district') <p class="text-[0.8rem] font-medium text-destructive mt-1">{{ $message }}</p> @enderror
                                </div>

                                <!-- State -->
                                <div class="space-y-2">
                                    <label class="text-sm font-medium leading-none text-foreground/80" for="state">State</label>
                                    <input class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30" 
                                           id="state" name="state" x-model="state" placeholder="State">
                                    @error('state') <p class="text-[0.8rem] font-medium text-destructive mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <!-- Full Address (Concatenated or Detailed) -->
                            <div class="space-y-2">
                                <label class="text-sm font-medium leading-none text-foreground/80" for="address">Full Address / House No.</label>
                                <textarea class="flex min-h-[80px] w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30" 
                                          id="address" name="address" x-model="address" placeholder="Residential Address / House No., Street, etc."></textarea>
                                @error('address') <p class="text-[0.8rem] font-medium text-destructive mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <!-- Bio -->
                        <div class="space-y-2 sm:col-span-2">
                            <label class="text-sm font-medium leading-none text-foreground/80" for="bio">
                                Short Biography
                            </label>
                            <div class="relative group">
                                <textarea class="flex min-h-[100px] w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm" 
                                          id="bio" name="bio" placeholder="Tell us a little bit about the user...">{{ old('bio', $user->bio ?? '') }}</textarea>
                            </div>
                            @error('bio') <p class="text-[0.8rem] font-medium text-destructive mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Password -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium leading-none text-foreground/80" for="password">
                                {!! 'Password ' . (isset($user) ? '(Optional)' : '<span class="text-destructive">*</span>') !!}
                            </label>
                            <div class="relative group">
                                <span class="absolute left-3 top-2.5 text-muted-foreground group-focus-within:text-primary transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                </span>
                                <input :type="showPassword ? 'text' : 'password'" 
                                       class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 pl-10 pr-10 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm" 
                                       id="password" name="password" placeholder="••••••••" {{ isset($user) ? '' : 'required' }}>
                                <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-2.5 text-muted-foreground hover:text-foreground transition-colors focus:outline-none">
                                    <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                                    <svg x-show="showPassword" style="display: none;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                            </div>
                            @if(isset($user))
                                <p class="text-[0.8rem] text-muted-foreground">Leave blank to keep existing password.</p>
                            @endif
                           @error('password') <p class="text-[0.8rem] font-medium text-destructive mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div class="space-y-2">
                             <label class="text-sm font-medium leading-none text-foreground/80" for="password_confirmation">
                                {!! 'Confirm Password ' . (isset($user) ? '(Optional)' : '<span class="text-destructive">*</span>') !!}
                            </label>
                            <div class="relative group">
                                <span class="absolute left-3 top-2.5 text-muted-foreground group-focus-within:text-primary transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                </span>
                                <input :type="showConfirm ? 'text' : 'password'" 
                                       class="flex h-10 w-full rounded-xl border border-input bg-background/50 px-3 pl-10 pr-10 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:border-primary/50 transition-all shadow-sm" 
                                       id="password_confirmation" name="password_confirmation" placeholder="••••••••" {{ isset($user) ? '' : 'required' }}>
                                <button type="button" @click="showConfirm = !showConfirm" class="absolute right-3 top-2.5 text-muted-foreground hover:text-foreground transition-colors focus:outline-none">
                                    <svg x-show="!showConfirm" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                                    <svg x-show="showConfirm" style="display: none;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Roles Column -->
                <div class="space-y-6">
                    <div>
                        <h2 class="text-lg font-semibold tracking-tight">Access Control</h2>
                        <p class="text-sm text-muted-foreground">Assign roles to define permissions.</p>
                    </div>

                    <div class="space-y-3">
                         @foreach($roles as $role)
                        <!-- Improved Interactive Card: Uses Alpine 'selectedRoles' to drive the class -->
                        <label class="group relative flex cursor-pointer rounded-xl border bg-background/40 p-4 shadow-sm transition-all hover:bg-accent hover:border-primary/30"
                               :class="selectedRoles.includes('{{ $role->name }}') ? 'border-primary bg-primary/5 shadow-primary/10' : 'border-border'">
                            
                            <div class="flex items-start lg:items-center gap-4 w-full">
                                <div class="flex h-5 items-center">
                                    <input type="checkbox" name="roles[]" value="{{ $role->name }}" 
                                           x-model="selectedRoles"
                                           class="h-4 w-4 rounded border-primary/50 text-primary shadow focus:ring-offset-0 focus:ring-2 focus:ring-primary/20 cursor-pointer transition-all checked:bg-primary checked:border-primary">
                                </div>
                                <div class="space-y-1 w-full">
                                    <div class="flex items-center justify-between">
                                        <span class="font-medium text-foreground transition-colors"
                                              :class="selectedRoles.includes('{{ $role->name }}') ? 'text-primary' : ''">
                                            {{ ucfirst($role->name) }}
                                        </span>
                                        <span class="inline-flex items-center rounded-full border border-border px-2 py-0.5 text-xs font-medium text-muted-foreground bg-muted/50"
                                              :class="selectedRoles.includes('{{ $role->name }}') ? 'bg-background/80' : ''">
                                            {{ $role->guard_name }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-muted-foreground/80 line-clamp-2"
                                       :class="selectedRoles.includes('{{ $role->name }}') ? 'text-foreground/70' : ''">
                                        Grants access to {{ $role->name }} features and permissions.
                                    </p>
                                </div>
                            </div>
                            <!-- Selection Highlight Border -->
                            <div class="absolute inset-0 rounded-xl border-2 border-primary opacity-0 transition-opacity pointer-events-none"
                                 :class="selectedRoles.includes('{{ $role->name }}') ? 'opacity-100' : 'opacity-0'"></div>
                        </label>
                        @endforeach
                    </div>
                    @error('roles') <p class="text-[0.8rem] font-medium text-destructive mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="flex items-center justify-end gap-4 pt-6 mt-6 border-t border-border/40">
                <a href="{{ $indexUrl }}" class="inline-flex items-center justify-center rounded-xl bg-muted px-4 py-2.5 text-sm font-medium text-muted-foreground transition-colors hover:bg-muted/80 hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-6 py-2.5 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/20 hover:bg-primary/90 hover:scale-[1.02] active:scale-95 transition-all duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span>{{ isset($user) ? 'Save Changes' : 'Create Account' }}</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
