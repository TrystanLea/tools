
<script src="https://cdn.jsdelivr.net/npm/vue@2"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flot/0.8.3/jquery.flot.min.js"></script> 
<script src="https://cdnjs.cloudflare.com/ajax/libs/flot/0.8.3/jquery.flot.time.min.js"></script> 
<script src="https://cdnjs.cloudflare.com/ajax/libs/flot/0.8.3/jquery.flot.selection.min.js"></script>

<script src="lib/ecodan.js?v=1"></script>
<script src="lib/feed.js?v=1"></script>

<div class="container" style="max-width:800px" id="app">
    <div class="row">
        <div class="col">
            <br>
            <h3>Super Simple Heat Loss</h3>
            <p>Explore the difference between custom measured assumptions and those typically used from the CIBSE domestic heating design guide.</p>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col">
            <label class="form-label">Total floor area</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="total_floor_area" @change="update">
                <span class="input-group-text">m2</span>
            </div>  
        </div>
        <div class="col">
            <label class="form-label">Floor Height</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="floor_height" @change="update">
                <span class="input-group-text">m</span>
            </div>
        </div>
        <div class="col">
            <label class="form-label">Number of Floors</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="floors" @change="update">
            </div>
        </div>
        <div class="col">
            <label class="form-label">House Type</label>
            <select class="form-select" v-model="house_type" @change="update">
                <option>Custom</option>
                <option>Detached Square 1:1</option>
                <option>Detached Rectangle 4:3</option>
                <option>Detached Rectangle 16:9</option>
                <option>Detached Rectangle 2:1</option>
                <option>Semi-detached Square 1:1</option>
                <option>Semi-detached Rectangle 4:3</option>
                <option>Semi-detached Rectangle 3:4</option>
                <option>Semi-detached Rectangle 5:3</option>
                <option>Semi-detached Rectangle 3:5</option>
                <option>Mid-terrace Square 1:1</option>
                <option>Mid-terrace Rectangle 4:3</option>
                <option>Mid-terrace Rectangle 3:4</option>
                <option>Mid-terrace Rectangle 5:3</option>
                <option>Mid-terrace Rectangle 3:5</option>                    
            </select>
        </div>
    </div>
    <div class="row">

        <div class="col">
            <label class="form-label">Length of external wall</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="external_wall_length" @change="update" :disabled="house_type != 'Custom'">
                <span class="input-group-text">m</span>
            </div>
        </div>
        <div class="col">
            <label class="form-label">Length of party wall</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="party_wall_length" @change="update" :disabled="house_type != 'Custom'">
                <span class="input-group-text">m</span>
            </div>  
        </div>

        <div class="col">
            <label class="form-label">Glazing Fraction</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="galzing_fraction" @change="update">
                <span class="input-group-text">%</span>
            </div>
        </div>
        <div class="col">
            <label class="form-label">Number of doors</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="number_of_doors" @change="update">
            </div>
        </div>
    </div>

    <hr>

    <table class="table">
        <tr>
            <th>Name</th>
            <th>Material</th>
            <th>Area</th>
            <th>U-value</th>
            <th>Heat Loss</th>
            <th>CIBSE</th>
        </tr>
        <tr>
            <td>Exteral Walls</td>
            <td>
                <select class="form-select" v-model="selected_external_wall_material" @change="update">
                    <option v-for="(value, key) in wall_materials">{{ key }}</option>
                </select>
            </td>
            <td>{{ elements["external wall"]["area"] | toFixed(1) }} <sub>m2</sub></td>
            <td>{{ elements["external wall"]["uvalue"] | toFixed(2) }}</td>
            <td>{{ elements["external wall"]["heat_loss"] | toFixed(0) }} W</td>
            <td>{{ elements["external wall"]["cibse_heat_loss"] | toFixed(0) }} W</td>
        </tr>
        <tr v-if="party_wall_length > 0">
            <td>Party Walls</td>
            <td>
                <select class="form-select" v-model="selected_party_wall_material" @change="update">
                    <option v-for="(value, key) in wall_materials">{{ key }}</option>
                </select>
            </td>
            <td>{{ elements["party wall"]["area"] | toFixed(1) }} <sub>m2</sub></td>
            <td>{{ elements["party wall"]["uvalue"] | toFixed(2) }}</td>
            <td>{{ elements["party wall"]["heat_loss"] | toFixed(0) }} W</td>
            <td>{{ elements["party wall"]["cibse_heat_loss"] | toFixed(0) }} W</td>
        </tr>
        <tr>
            <td>Windows</td>
            <td>
                <select class="form-select" v-model="selected_window_material" @change="update">
                    <option v-for="(value, key) in window_materials">{{ key }}</option>
                </select>
            </td>
            <td>{{ elements["windows"]["area"] | toFixed(1) }} <sub>m2</sub></td>
            <td>{{ elements["windows"]["uvalue"] | toFixed(2) }}</td>
            <td>{{ elements["windows"]["heat_loss"] | toFixed(0) }} W</td>
        </tr>
        <tr>
            <td>Doors</td>
            <td>
                <select class="form-select" v-model="selected_door_material" @change="update">
                    <option v-for="(value, key) in door_materials">{{ key }}</option>
                </select>
            </td>
            <td>{{ elements["doors"]["area"] | toFixed(1) }} <sub>m2</sub></td>
            <td>{{ elements["doors"]["uvalue"] | toFixed(2) }}</td>
            <td>{{ elements["doors"]["heat_loss"] | toFixed(0) }} W</td>
        </tr>
        <tr>
            <td>Floor</td>
            <td>
                <select class="form-select" v-model="selected_floor_material" @change="update">
                    <option v-for="(value, key) in floor_materials">{{ key }}</option>
                </select>
            </td>
            <td>{{ elements["floor"]["area"] | toFixed(1) }} <sub>m2</sub></td>
            <td>{{ elements["floor"]["uvalue"] | toFixed(2) }}</td>
            <td>{{ elements["floor"]["heat_loss"] | toFixed(0) }} W</td>
        </tr>
        <tr>
            <td>Roof</td>
            <td>
                <select class="form-select" v-model="selected_roof_material" @change="update">
                    <option v-for="(value, key) in roof_materials">{{ key }}</option>
                </select>
            <td>{{ elements["roof"]["area"] | toFixed(1) }} <sub>m2</sub></td>
            <td>{{ elements["roof"]["uvalue"] | toFixed(2) }}</td>
            <td>{{ elements["roof"]["heat_loss"] | toFixed(0) }} W</td>
        </tr>
    </table>

    <hr>

    <div class="row">
        <div class="col">
            <label class="form-label" for="indoor_temperature">Custom average indoor temperature</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="boundaries['internal']" @change="update">
                <span class="input-group-text">°C</span>
            </div>
        </div>

        <div class="col">
            <label class="form-label" for="external_temperature">Measured external desgin temperature (99.6%)</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="boundaries['external']" @change="update">
                <span class="input-group-text">°C</span>
            </div>
        </div>

        <div class="col" v-if="party_wall_length > 0">
            <label class="form-label" for="neighbour_temperature">Custom neighbouring property temperature</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="boundaries['neighbour']" @change="update">
                <span class="input-group-text">°C</span>
            </div>
        </div>
    </div>

    <hr>

    <div class="row">
        <div class="col">
            <label class="form-label" for="indoor_temperature">Standard average indoor temperature</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="boundaries_cibse['internal']" @change="update">
                <span class="input-group-text">°C</span>
            </div>
        </div>

        <div class="col">
            <label class="form-label" for="external_temperature">External design temperature from CIBSE table (99.6%)</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="boundaries_cibse['external']" @change="update">
                <span class="input-group-text">°C</span>
            </div>
        </div>

        <div class="col" v-if="party_wall_length > 0">
            <label class="form-label" for="neighbour_temperature">Standard neighbour temperature (assumed unoccupied)</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="boundaries_cibse['neighbour']" @change="update">
                <span class="input-group-text">°C</span>
            </div>
        </div>
    </div>

    <hr>

    <div class="row">

        <div class="col">
            <label class="form-label" for="airChangeRate">Custom air change rate<br>(Measured or other evidence based)</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="airChangeRate" @change="update">
                <span class="input-group-text">ACH</span>
            </div>
        </div>

        <div class="col">
            <label class="form-label" for="building_age">CIBSE Air Change Rates from Table 3.8<br>(No clear referenced evidence given by CIBSE)</label>
            <select class="form-select" v-model="building_age" @change="update">
                <option>Pre-2000 (1.8 ACH)</option>
                <option>Post-2000 (1.25 ACH)</option>
                <option>Post-2006 (0.5 ACH)</option>
            </select>
        </div>
    </div>

    <hr>

    <div class="row">
        <div class="col">
            <b>Heat loss</b>
            <h2>{{ totalHeatLoss*0.001 | toFixed(1) }} kW</h2>
        </div>  
        <div class="col">
            <b>Heat loss per m2</b>
            <h2>{{ Wm2 | toFixed(0) }} W/m2</h2>
        </div>
        <div class="col">
            <b>Heat pump size*</b>
            <h2>{{ totalHeatLoss*0.001*1.4 | toFixed(1) }} kW</h2>
        </div> 
        <div class="col">
            <b>Number of 1200x600 K2 radiators<br>
            <span style="color:green">{{ rads40 | toFixed(1) }}x @ 40°C</span><br>
            <span style="color:red">{{ rads50 | toFixed(1) }}x @ 50°C</span></b>
        </div>

    </div>
    <p>* Heat pump sizing based on 40% margin to account for lower than datasheet average heat output during defrost conditions (typically 10-30%). Careful consideration based on available real world defrost output data required. Optimal margin will vary from one heat pump make & model to another and may vary in the 10-50% range.</p>

    <hr>

    <div class="row">
        <div class="col">
            <b>CIBSE Heat loss</b>
            <h2>{{ totalHeatLossCIBSE*0.001 | toFixed(1) }} kW</h2>
        </div>  
        <div class="col">
            <b>Heat loss per m2</b>
            <h2>{{ totalHeatLossCIBSE/total_floor_area | toFixed(0) }} W/m2</h2>
        </div>
        <div class="col">
            <b>Heat pump size</b>
            <h2>{{ totalHeatLossCIBSE*0.001 | toFixed(1) }} kW</h2>
        </div>   
        <div class="col">
            <b>Number of 1200x600 K2 radiators<br>
            <span style="color:green">{{ rads40_cibse | toFixed(1) }}x @ 40°C</span><br>
            <span style="color:red">{{ rads50_cibse | toFixed(1) }}x @ 50°C</span></b>
        </div>          
    </div>


</div>

<script>

    var app = new Vue({
        el: '#app',
        data: {


            house_type: "Custom",
            external_wall_length: 11.4,
            party_wall_length: 17.8,

            total_floor_area: 77,
            floors: 2,
            floor_height: 2.4,
            galzing_fraction: 16,
            number_of_doors: 2,
            airChangeRate: 0.6,
            building_age: "Pre-2000 (1.8 ACH)", // Pre-2000 (1.8 ACH), Post-2000 (1.25 ACH), Post-2006 (0.5 ACH)

            boundaries: {
                "internal": 19.4,
                "external": -1.4,
                "ground": 10.6,
                "neighbour": 18
            },

            boundaries_cibse: {
                "internal": 19.4,
                "external": -3.6,
                "ground": 10,
                "neighbour": 10     
            },

            selected_external_wall_material: "solid stone",
            selected_party_wall_material: "solid stone",
            selected_window_material: "double glazed",
            selected_door_material: "standard",
            selected_floor_material: "slab to ground",
            selected_roof_material: "200mm insulated",
            
            wall_materials: {
                "cavity wall - insulated": {"uvalue":0.45},
                "cavity wall - uninsulated": {"uvalue":0.85},
                "solid stone": {"uvalue":1.5, "cibse": 2.23},
                "solid brick": {"uvalue":1.5, "cibse": 2.11},
                "insulated 300mm": {"uvalue":0.12},
                "insulated 200mm": {"uvalue":0.18},
                "insulated 100mm": {"uvalue":0.33},
            },

            window_materials: {
                "double glazed": {"uvalue":2.8},
                "triple glazed": {"uvalue":1.0},
                "single glazed": {"uvalue":4.8},
            },

            door_materials: {
                "standard": {"uvalue":2.8},
                "passivhaus": {"uvalue":0.8},
            },

            floor_materials: {
                "slab to ground": {"uvalue":0.6},
                "insulated slab": {"uvalue":0.3},
                "suspended timber": {"uvalue":1.0},
            },

            roof_materials: {
                "300mm insulated": {"uvalue":0.12},
                "200mm insulated": {"uvalue":0.18},
                "100mm insulated": {"uvalue":0.33},
                "uninsulated": {"uvalue":2.0},
            },

            door_height: 2.0,
            door_width: 0.9,

            elements: {
                "external wall": {"area":0, "uvalue":0, "boundary":"external"},
                "party wall": {"area":0, "uvalue":0, "boundary":"neighbour"},
                "floor": {"area":0, "uvalue":0, "boundary":"ground"},
                "roof": {"area":0, "uvalue":0, "boundary":"external"},
                "windows": {"area":0, "uvalue":0, "boundary":"external"},
                "doors": {"area":0, "uvalue":0, "boundary":"external"},
            },
            // -------------------
            // Outputs

            totalHeatLoss: 0,
            totalHeatLossCIBSE: 0,
            Wm2: 0,
            Wm2_cibse: 0,
            rads40: 0,
            rads50: 0,
            rads40_cibse: 0,
            rads50_cibse: 0
            
        },
        methods: {
            update: function () {
                app.model();
            },

            parse_house_type: function (floor_area) {
                // split string by space
                if (this.house_type !== "Custom") {
                    let house_type_parts = this.house_type.split(" ");
                    let type = house_type_parts[0];
                    // let shape = house_type_parts[1];
                    let ratio = house_type_parts[2];

                    // convert ratio to float, split by colon
                    if (ratio != undefined) {
                        ratio = ratio.split(":");
                        ratio = parseFloat(ratio[0]) / parseFloat(ratio[1]);
                    }

                    if (type === "Detached") {
                        party = 0;
                    } else if (type === "Semi-detached") {
                        party = 1;
                    } else if (type === "Mid-terrace") {
                        party = 2;
                    }

                    let length = Math.sqrt( floor_area / ratio)
                    let width = length * ratio;
                    this.external_wall_length = ((2 * width) + ((2 - party) * length)).toFixed(2);
                    this.party_wall_length = (length * party).toFixed(2);
                }
            },

            calc_number_of_rads: function (total_heat_loss, design_flow_temperature, indoor_temperature) {
                let DT1 = 5
                let MWT = design_flow_temperature - DT1 / 2
                let DT2 = MWT - indoor_temperature
                let Rated_Heat_Output = total_heat_loss / ((DT2 / 50) ** 1.3)

                let number_of_k2_1200x600 = Rated_Heat_Output / 2050
                return number_of_k2_1200x600;
            },

            model: function() {

                let floor_area = this.total_floor_area / this.floors;

                this.parse_house_type(floor_area);

                this.elements["external wall"] = {
                    "area":this.external_wall_length*(this.floor_height * this.floors), 
                    "uvalue":this.wall_materials[this.selected_external_wall_material]["uvalue"],
                    "cibse":this.wall_materials[this.selected_external_wall_material]["cibse"],
                    "boundary":"external"
                }

                this.elements["party wall"] = {
                    "area":this.party_wall_length*(this.floor_height * this.floors),
                    "uvalue":this.wall_materials[this.selected_party_wall_material]["uvalue"], 
                    "cibse":this.wall_materials[this.selected_party_wall_material]["cibse"],
                    "boundary":"neighbour"
                }

                this.elements["floor"] = {
                    "area":floor_area, 
                    "uvalue":this.floor_materials[this.selected_floor_material]["uvalue"], 
                    "boundary":"ground"
                }

                this.elements["roof"] = {
                    "area":floor_area, 
                    "uvalue":this.roof_materials[this.selected_roof_material]["uvalue"], 
                    "boundary":"external"
                }

                this.elements["windows"] = {
                    "area": this.galzing_fraction * 0.01 * this.elements["external wall"]["area"],
                    "uvalue": this.window_materials[this.selected_window_material]["uvalue"], 
                    "boundary":"external"
                }

                this.elements["doors"] = {
                    "area": this.door_height * this.door_width * this.number_of_doors,
                    "uvalue":this.door_materials[this.selected_door_material]["uvalue"], 
                    "boundary":"external"
                }

                // Subtract windows and doors from external wall area
                this.elements["external wall"]["area"] -= this.elements["windows"]["area"];
                this.elements["external wall"]["area"] -= this.elements["doors"]["area"];

                // Calculate WK and heat loss for each element
                let totalHeatLoss = 0;
                let totalHeatLossCIBSE = 0;

                for (const element in this.elements) {
                    const area = this.elements[element]["area"];
                    const boundary = this.elements[element]["boundary"];

                    const uValue = this.elements[element]["uvalue"];
                    const deltaT = this.boundaries['internal'] - this.boundaries[boundary];
                    const WK = area * uValue;
                    const heatLoss = WK * deltaT;

                    this.elements[element]["heat_loss"] = heatLoss;
                    totalHeatLoss += heatLoss;

                    // -----------------------

                    let uValue_cibse = uValue;
                    if (this.elements[element]["cibse"] != undefined) {
                        uValue_cibse = this.elements[element]["cibse"];
                    }
                    const deltaT_cibse = this.boundaries_cibse['internal'] - this.boundaries_cibse[boundary];
                    const WK_cibse = area * uValue_cibse;
                    const heatLoss_cibse = WK_cibse * deltaT_cibse;

                    this.elements[element]["cibse_heat_loss"] = heatLoss_cibse;
                    totalHeatLossCIBSE += heatLoss_cibse;
                }

                // Calculate total floor area
                const buildingVolume = this.total_floor_area * this.floor_height;
                const infiltrationWK = this.airChangeRate * 0.33 * buildingVolume;
                const infiltrationHeatLoss = infiltrationWK * (this.boundaries['internal'] - this.boundaries["external"]);
                totalHeatLoss += infiltrationHeatLoss;
                this.totalHeatLoss = totalHeatLoss;
                this.Wm2 = totalHeatLoss / this.total_floor_area;

                this.rads40 = this.calc_number_of_rads(totalHeatLoss, 40, this.boundaries['internal']);
                this.rads50 = this.calc_number_of_rads(totalHeatLoss, 50, this.boundaries['internal']);

                // ----------------------------

                let cibse_air_change_rate = 0;
                if (this.building_age === "Pre-2000 (1.8 ACH)") {
                    cibse_air_change_rate = 1.8;
                } else if (this.building_age === "Post-2000 (1.25 ACH)") {
                    cibse_air_change_rate = 1.25;
                } else if (this.building_age === "Post-2006 (0.5 ACH)") {
                    cibse_air_change_rate = 0.5;
                }

                const cibse_infiltrationWK = cibse_air_change_rate * 0.33 * buildingVolume;
                const cibse_infiltrationHeatLoss = cibse_infiltrationWK * (this.boundaries_cibse['internal'] - this.boundaries_cibse["external"]);
                totalHeatLossCIBSE += cibse_infiltrationHeatLoss;
                this.totalHeatLossCIBSE = totalHeatLossCIBSE;
                this.Wm2_cibse = totalHeatLossCIBSE / this.total_floor_area;

                this.rads40_cibse = this.calc_number_of_rads(totalHeatLossCIBSE, 40, this.boundaries_cibse['internal']);
                this.rads50_cibse = this.calc_number_of_rads(totalHeatLossCIBSE, 50, this.boundaries_cibse['internal']);
            }
        },
        filters: {
            toFixed: function (val, dp) {
                if (isNaN(val)) {
                    return val;
                } else {
                    return val.toFixed(dp)
                }
            }
        }    
    });

    app.model();

</script>
