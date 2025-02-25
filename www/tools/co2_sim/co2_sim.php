
<script src="https://cdn.jsdelivr.net/npm/vue@2"></script>

<script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flot/0.8.3/jquery.flot.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flot/0.8.3/jquery.flot.time.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flot/0.8.3/jquery.flot.selection.min.js"></script>


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/fontawesome.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/solid.min.css">

<div class="container" style="max-width:1400px" id="app">
    <div class="row">
        <div class="col">
            <br>
            <h3>Building indoor CO2 simulator</h3>
            <p>Explore effect of building occupancy & air change rate on indoor CO2 concentrations.</p>
        </div>
    </div>
    <div class="row">
        <div id="graph_bound" style="width:100%; height:400px; position:relative; ">
            <div id="graph"></div>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col">
            <label class="form-label">Mean</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" :value="results.mean | toFixed(0)" disabled>
                <span class="input-group-text">ppm</span>
            </div>
        </div>
        <div class="col">
            <label class="form-label">Min</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" :value="results.min | toFixed(0)" disabled>
                <span class="input-group-text">ppm</span>
            </div>
        </div>
        <div class="col">
            <label class="form-label">Max</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" :value="results.max | toFixed(0)" disabled>
                <span class="input-group-text">ppm</span>
            </div>
        </div>
        <div class="col">
            <label class="form-label">Mean ACH</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" :value="average_air_change_rate | toFixed(2)" disabled>
                <span class="input-group-text">ACH</span>
            </div>
        </div>
        <div class="col">
            <label class="form-label">Selection ACH</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" :value="selection_air_change_rate | toFixed(2)" disabled>
                <span class="input-group-text">ACH</span>
            </div>
        </div>
        <div class="col">
            <label class="form-label">Simulate</label>
            <span class="input-group mb-3">

                <button type="button" class="btn btn-warning" @click="simulate">Refine</button>

            </span>
        </div>
    </div>
    
    <div class="row">
        <div class="col">
            <p><b>Building volume</b></p>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="building.volume" @change="volume_change" />
                <span class="input-group-text">m3</span>
            </div>
        </div>
        <div class="col">
            <p><b>Ambient CO2 level</b></p>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="ambient_co2" @change="simulate" />
                <span class="input-group-text">ppm</span>
            </div>
        </div>
        <div class="col">
            <p><b>Exponential fit baseline</b></p>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="exp_baseline_co2" @change="update_exp_fit" />
                <span class="input-group-text">ppm</span>
            </div>
        </div>
        <div class="col">
            <p><b>Internal temperature</b></p>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="internal_temp" @change="simulate" />
                <span class="input-group-text">°C</span>     
            </div>
        </div>
        <div class="col">
            <p><b>Outside temperature</b></p>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="outside_temp" @change="simulate" />
                <span class="input-group-text">°C</span>     
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h4>Schedule</h4>
                    <table class="table">
                        <tr>
                            <td></td>
                            <th v-for="(p,index) in people">Person {{ index+1 }}</th>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>

                        <tr>
                            <td>Gender</td>
                            <th v-for="(p,index) in people">
                                <select class="form-control" v-model="p.gender" @change="simulate" style="width:140px">
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                            </th>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        
                        <tr>
                            <td>Age</td>
                            <th v-for="(p,index) in people">
                                <input type="text" class="form-control" v-model.number="p.age" @change="simulate" style="width:140px" />
                            </th>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>

                        <tr>
                            <th>Time</th>
                            <th v-for="(p,index) in people">Person {{ index+1 }}</th>
                            <th>CO2 Production Rate</th>
                            <th>Air change rate</th>
                            <th>Heat loss</th>
                            <th><button class="btn" @click="add_space"><i class="fas fa-plus"></i></button></th>
                        </tr>

                        <tr v-for="(item,index) in schedule">
                            <td><input type="text" class="form-control" v-model="item.start" @change="simulate" style="width:75px" /></td>
                            <td v-for="(p,person_index) in people">
                                <select type="text" class="form-control" v-model="item.person_activities[person_index]" @change="simulate" style="width:140px">
                                    <option v-for="(activity,activity_key) in activity_met">{{ activity_key }}</option>
                                </select>

                            </td>
                            <td>
                                <div class="input-group mb-3" style="width:120px">
                                    <input type="text" class="form-control" :value="item.co2_production | toFixed(1)"  disabled/>
                                    <span class="input-group-text">L/h</span>
                                </div>
                            </td>
                            <td>
                                <div class="input-group mb-3" style="width:120px">
                                    <input type="text" class="form-control" v-model.number="item.air_change_rate" @change="simulate" />
                                    <span class="input-group-text">ACH</span>
                                </div>
                            </td>
                            <td>
                                <div class="input-group mb-3" style="width:120px">
                                    <input type="text" class="form-control" v-model.number="item.heatloss" disabled />
                                    <span class="input-group-text">W</span>
                                </div>
                            </td>
                            <td><button class="btn" @click="delete_space(index)"><i
                                        class="fas fa-trash"></i></button></td>
                        </tr>
                    </table>

                    <p>Total C02 Production {{ total_production | toFixed(1) }} L/day</p>
                    <p>Average heat loss: {{ heatloss_kwh / 0.024 | toFixed(0) }} W, {{ heatloss_kwh | toFixed(1) }} kWh/d</p>
                    <p><i>MEV & MVHR options not yet added.</i></p>
                </div>
            </div>
            <br>
        </div>
        
    </div>
</div>
<script src="<?php echo $path; ?>calculate_co2_rate.js?v=4"></script>
<script src="<?php echo $path; ?>co2_sim.js?v=13"></script>
