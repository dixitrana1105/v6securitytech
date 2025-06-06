<x-layout.default>
    <div class="relative flex min-h-screen items-center justify-center bg-[url(/assets/images/auth/map.png)] bg-cover bg-center bg-no-repeat px-6 py-10 dark:bg-[#060818] sm:px-16">

    <div class="panel shadow-lg rounded-lg p-8 max-w-6xl mx-auto " style="border-color: #a8acaf; border-width: medium;">
        <div class="flex justify-center mb-4">
            <h1 class="text-2xl font-bold">Edit Building</h1>
        </div>

    <style>
        .alert-danger {
            color: red;
        }

        .alert-success {
            color: #5CB85C;
        }
    </style>
        <form method="POST" action="{{ route('super-admin.building-update', $value->id) }}">
            @csrf
            @method('PUT')
        <div class="flex justify-between items-center">
            <div></div>
            <div>
                <label for="registerDate"><strong>Register Date</strong></label>
                <input  type="date" name="date" value="{{ $value->date ?? '' }}" style="width: 100%" class="form-input" required readonly/>
            </div>
        </div>
        <br>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="buildingId"><strong>Building ID</strong></label>
                    <input id="buildingId" type="text" name="buildingId" value="{{ $value->building_id ?? '' }}" placeholder="Auto Generate" class="form-input" readonly/>
                    <input type="hidden" name="type" value="building"/>
                </div>
                <div>
                    <label for="buildingname"><strong>Building Name</strong></label>
                    <input id="buildingname" name="buildingname" value="{{ $value->name ?? '' }}" type="text" placeholder="Enter Building Name" class="form-input" required/>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="typeofbuilding"><strong>Type of Building</strong></label>
                    <select id="typeofbuilding" name="typeofbuilding"  class="form-input" required>
                        <option value="" selected>Select Option</option>
                        <option value="1" @if ($value->type_of_building == '1') selected @endif>Commercial Building</option>
                        <option value="2" @if ($value->type_of_building == '2') selected @endif>Residential Building</option>
                        <option value="3" @if ($value->type_of_building == '3') selected @endif>Commercial + Residential</option>
                    </select>
                </div>
                <div>
                    <label for="email"><strong>Email ID</strong></label>
                    <div class="flex">
                    <div class="bg-[#eee] flex justify-center items-center ltr:rounded-l-md rtl:rounded-r-md px-3 font-semibold border ltr:border-r-0 rtl:border-l-0 border-[#e0e6ed] dark:border-[#17263c] dark:bg-[#1b2e4b]">@</div>
                    <input id="email" type="email" name="email" placeholder="Enter Email" value="{{ $value->email ?? ''}}" class="form-input ltr:rounded-l-none rtl:rounded-r-none"  required/>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="name"><strong>Contact Person</strong></label>
                    <input id="name" type="text" name="name" placeholder="name" value="{{ $value->contact_person ?? ''}}" class="form-input" required/>
                </div>
                <div>
                    <label for="contact"><strong>Contact Number</strong></label>
                    <input id="contact" name="contact" type="number" placeholder="Enter contact number" value="{{ $value->contact_number ?? ''}}" class="form-input" required />
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="notenant"><strong>No. Of Tenant</strong></label>
                    <input id="notenant" name="notenant" type="number" placeholder="no tenant person" value="{{ $value->no_of_tenant ?? ''}}" class="form-input" required/>
                </div>
                <div>
                    <label for="security"><strong>No. Security Person</strong></label>
                    <input id="security" name="security" type="number" placeholder="no security person" value="{{ $value->no_security_person ?? ''}}" class="form-input" required/>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="address_1"><strong>Address 1</strong></label>
                    <input id="address_1" type="text" name="address_1" placeholder="address 1" value="{{ $value->address_1 ?? ''}}" class="form-input" required />
                </div>
                <div>
                    <label for="address_2"><strong>Address 2</strong></label>
                    <input id="address_2" name="address_2" type="text" placeholder="address 2" value="{{ $value->address_2 ?? ''}}" class="form-input"  />
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label for="business_name"><strong>Business Name</strong></label>
                    <input id="business_name" type="text" name="business_name" placeholder="business name" value="{{ $value->business_name ?? ''}}" class="form-input"  />
                </div>
                <div>
                    <label for="password"><strong>Password</strong></label>
                    <input id="password" type="password" name="password" placeholder="password" class="form-input"  />
                </div>
                <div>
                    <label for="secretkey"><strong>Secret Key</strong></label>
                    <input id="secretkey" name="secretkey" type="text" placeholder="secret Key" value="{{ $value->secret_key ?? ''}}" class="form-input" />
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label for="country"><strong>Country</strong></label>
                    <select id="countryDropdown" class="selectize form-input" name="country"
                    onchange="filterLocations()">
                    @isset($country)
                        @foreach ($country as $countryList)
                            <option value="{{ $countryList->id }}"
                                {{ $value->country == $countryList->id ? 'selected' : '' }}>
                                {{ $countryList->name }}</option>
                        @endforeach
                    @endisset
                    </select>
                </div>
                <div>
                    <label for="state"><strong>State</strong></label>
                    <select id="stateDropdown" class="form-input" name="state" onchange="filterLocations()">
                        <option value="{{ $value->state }}">{{ $value->State->name }}</option>
                    </select>
                </div>
                <div>
                    <label for="city"><strong>City</strong></label>
                    <select id="cityDropdown" class="form-input" name="city">
                        <option value="{{ $value->city }}">{{ $value->City->name }}</option>
                    </select>
                </div>
            </div>
            <div>
                <button type="submit" class="btn btn-primary !mt-6">Submit</button>
            </div>
        </form>
    </div>
    </div>
    <script>
        function filterLocations() {
            var countryId = document.getElementById('countryDropdown').value;
            var stateDropdown = document.getElementById('stateDropdown');
            var cityDropdown = document.getElementById('cityDropdown');

            // Get the currently selected state (if any)
            var selectedStateId = stateDropdown.value;

            // Clear existing options
            stateDropdown.innerHTML = '<option value="">Select State</option>';
            cityDropdown.innerHTML = '<option value="">Select City</option>';

            // Show states corresponding to the selected country
            var states = {!! json_encode($states) !!};
            states.forEach(function(state) {
                if (state.country_id == countryId) {
                    var option = document.createElement("option");
                    option.value = state.id;
                    option.text = state.name;
                    if (state.id == selectedStateId) {
                        option.selected = true; // Set selected attribute for the selected state
                    }
                    stateDropdown.appendChild(option);
                }
            });

            // Show cities corresponding to the selected state
            var cities = {!! json_encode($cities) !!};
            cities.forEach(function(city) {
                if (city.state_id == selectedStateId) {
                    var option = document.createElement("option");
                    option.value = city.id;
                    option.text = city.name;
                    cityDropdown.appendChild(option);
                }
            });
        }
    </script>

    </x-layout.default>

