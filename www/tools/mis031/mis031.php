<script src="https://cdn.jsdelivr.net/npm/vue@2"></script>
<script src="<?php echo $path; ?>mis031.js?v=1"></script>

<div class="container mt-3" style="max-width:800px" id="app">
    <div class="row">
        <div class="col">
            <h3>MIS031</h3>
            <p>Heat Pump System Performance Estimate</p>
        </div>
    </div>
    <hr>
    <div class="row">
        <p><b>Your energy requirements</b></p>
        <div class="col">
            <label class="form-label">EPC Space Heating Demand</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="EPC_space_heating_demand" @change="update">
                <span class="input-group-text">kWh</span>
            </div>
        </div>

        <div class="col">
            <label class="form-label">EPC Hot Water Heating Demand</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="EPC_hot_water_heating_demand"
                    @change="update">
                <span class="input-group-text">kWh</span>
            </div>
        </div>

        <div class="col">
            <label class="form-label">EPC Floor Area</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="EPC_floor_area" @change="update">
                <span class="input-group-text">m²</span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <label class="form-label">Property Postcode</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model="property_postcode" @change="update">
            </div>
        </div>
        <div class="col">
            <label class="form-label">Design Outdoor Temperature</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model="design_outdoor_temperature" disabled>
                <span class="input-group-text">°C</span>
            </div>
        </div>
        <div class="col">
            <label class="form-label">Degree Days</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model="degree_days" disabled>
                <span class="input-group-text">days</span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <label class="form-label">Specific Heat Loss</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" :value="P_specific | toFixed(0)" disabled>
                <span class="input-group-text">W/K</span>
            </div>
        </div>
        <div class="col">
            <label class="form-label">Total Heat Loss</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" :value="heat_loss | toFixed(0)" disabled>
                <span class="input-group-text">W</span>
            </div>
        </div>
        <div class="col">
            <label class="form-label">Heat Loss per m²</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" :value="wm2 | toFixed(0)" disabled>
                <span class="input-group-text">W/m²</span>
            </div>
        </div>
        <div class="col">
            <label class="form-label">Heat Pump Capacity</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" :value="heat_pump_capacity | toFixed(1)" disabled>
                <span class="input-group-text">kW</span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <label class="form-label">Heat Pump Type</label>
            <div class="input-group mb-3">
                <select class="form-select" v-model="hp_type" @change="update">
                    <option value="ASHP">ASHP</option>
                    <option value="GSHP">GSHP</option>
                </select>
            </div>
        </div>
        <div class="col">
            <label class="form-label">Design Flow Temperature</label>
            <div class="input-group mb-3">
                <select class="form-select" v-model="design_flow_temperature" @change="update">
                    <option>Up to 35°C</option>
                    <option>36 to 40°C</option>
                    <option>41 to 45°C</option>
                    <option>46 to 50°C</option>
                    <option>51 to 55°C</option>
                    <option>56 to 60°C</option>
                    <option>61 to 65°C</option>
                </select>
            </div>
        </div>

        <div class="col">
            <label class="form-label">Seasonal Performance Factor</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" :value="SPF | toFixed(1)" disabled>
            </div>
        </div>

    </div>

    <div class="row">


        <div class="col">
            <table class="table">
                <tr>
                    <th></th>
                    <th>Heat demand</th>
                    <th>Electric demand</th>
                    <th>SPF</th>
                </tr>
                <tr>
                    <td>Space heating</td>
                    <td>{{ EPC_space_heating_demand | toFixed(0) }} kWh</td>
                    <td>{{ space_heating_electric_demand | toFixed(0) }} kWh</td>
                    <td>{{ SPF | toFixed(1) }}</th>
                </tr>
                <tr>
                    <td>Hot water</td>
                    <td>{{ EPC_hot_water_heating_demand | toFixed(0) }} kWh</td>
                    <td>{{ hot_water_electric_demand | toFixed(0) }} kWh</td>
                    <td>1.7</th>
                </tr>
                <tr>
                    <td>Total</td>
                    <td>{{ EPC_space_heating_demand + EPC_hot_water_heating_demand | toFixed(0) }} kWh</td>
                    <td>{{ total_eletric_demand | toFixed(0) }} kWh</td>
                    <td>{{ combined_SPF | toFixed(2) }}</th>
                </tr>

            </table>
        </div>

    </div>

</div>


<script>
    var app = new Vue({
        el: '#app',
        data: {
            EPC_space_heating_demand: 18341,
            EPC_hot_water_heating_demand: 3448,
            EPC_floor_area: 108,
            property_postcode: 'LL55',
            postcode_table: postcode_table,
            design_internal_temperature: 21,
            design_outdoor_temperature: 0,
            degree_days: 0,
            P_specific: 0,
            heat_loss: 0,
            wm2: 0,
            heat_pump_capacity: 0,
            hp_type: 'ASHP',
            design_flow_temperature: '36 to 40°C',
            SPF: 0.0,
            space_heating_electric_demand: 0,
            hot_water_electric_demand: 0,
            total_eletric_demand: 0,
            combined_SPF: 0

        },
        methods: {
            update: function() {



                app.model();
            },

            model: function() {
                // Fetch the postcode prefix and use it to get the design outdoor temperature and degree days
                var postcode_prefix = this.property_postcode.substring(0, 2);
                this.design_outdoor_temperature = this.postcode_table[postcode_prefix][0];
                this.degree_days = this.postcode_table[postcode_prefix][1];

                // e. Calculate the property specific heat loss
                this.P_specific = (1000 * this.EPC_space_heating_demand) / (24 * this.degree_days);
                // f. Calculate the total heat loss
                this.heat_loss = this.P_specific * (this.design_internal_temperature - this.design_outdoor_temperature);
                // g. Calculate the heat loss per m²
                this.wm2 = this.heat_loss / this.EPC_floor_area;
                // Estimate the heat pump capacity (in kW)
                this.heat_pump_capacity = this.heat_loss * 0.001;

                this.SPF = SPF_table[this.hp_type][this.design_flow_temperature];

                this.space_heating_electric_demand = this.EPC_space_heating_demand / this.SPF;
                this.hot_water_electric_demand = this.EPC_hot_water_heating_demand / 1.7;
                this.total_eletric_demand = this.space_heating_electric_demand + this.hot_water_electric_demand;

                this.combined_SPF = (this.EPC_space_heating_demand + this.EPC_hot_water_heating_demand) / this.total_eletric_demand;


            }
        },
        filters: {
            toFixed: function(val, dp) {
                if (isNaN(val)) {
                    return val;
                } else {
                    return val.toFixed(dp)
                }
            }
        }
    });

    app.update();
</script>