<x-layout.default>
    <div
        class="relative flex min-h-screen items-center justify-center bg-[url(/assets/images/auth/map.png)] bg-cover bg-center bg-no-repeat px-6 py-10 dark:bg-[#060818] sm:px-16">
        <div class="panel shadow-lg rounded-lg p-8 max-w-4xl mx-auto"
            style="border-color: #a8acaf; border-width: medium;">
            <div class="flex justify-center mb-4">
                <h1 class="text-2xl font-bold">Create Card</h1>
            </div>

            <form action="{{ route('super-admin.card-store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-1 gap-5">
                    <div>
                        <label for="building_id"><strong>Select Building</strong></label>
                        <select id="building_id" name="building_id" class="form-input" required>
                            <option value="" disabled selected>Select Building</option>
                            @foreach ($buildings as $building)
                                <option value="{{ $building->id }}">{{ $building->name }}</option>
                            @endforeach
                        </select>
                    </div>


                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-5">
                    <div>
                        <label for="serial_id"><strong>Serial ID</strong></label>
                        <input id="serial_id" name="serial_id" type="text" placeholder="Enter Serial ID"
                            class="form-input" required>
                    </div>
                    <div>
                        <label for="assign_status"><strong>Assign Status</strong></label>
                        <select id="assign_status" name="assign_status" class="form-input" required>
                            <option value="" disabled selected>Select Status</option>
                            <option value="unassigned">Unassigned</option>
                            <option value="assigned">Assigned</option>
                        </select>
                    </div>
                </div>

                <div>
                    <button type="submit" class="btn btn-primary !mt-6">Create Card</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const buildingSelect = document.getElementById("building_id");
            const readerSelect = document.getElementById("reader_id");

            buildingSelect.addEventListener("change", function () {
                console.log("buildingSelectValue", this.value)
                const selectedBuilding = this.value;
                console.log("readerSelect", readerSelect)
                // Show only readers that belong to the selected building
                for (let option of readerSelect.options) {
                    console.log(option)
                    if (option.value === "") continue; // Keep the placeholder

                    const readerBuildingId = option.getAttribute("data-building");

                    if (readerBuildingId === selectedBuilding) {
                        option.style.display = "block";
                    } else {
                        option.style.display = "none";
                    }
                }

                // Reset reader selection
                readerSelect.value = "";
            });
        });
    </script>
</x-layout.default>