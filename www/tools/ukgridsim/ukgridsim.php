<script src="https://cdn.jsdelivr.net/npm/vue@2"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flot/0.8.3/jquery.flot.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flot/0.8.3/jquery.flot.time.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flot/0.8.3/jquery.flot.selection.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flot/0.8.3/jquery.flot.stack.min.js"></script>

<script src="<?php echo $path_lib; ?>feed.js?v=1"></script>
<script src="<?php echo $path_lib; ?>vis.helper.js?v=1"></script>

<div class="container-fluid" id="app">
    <div class="row" style="background-color: #f0f0f0">
        <div class="col mt-3">
            <p><b>UK Grid Sim</b> Can you match supply and demand on the UK grid? <i>Real demand and generation data April 2023 to April 2024</i></p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mt-3">

            <!-- button group nav + - < > -->
            <div class="btn-group" style="float:right; margin-right:28px">
                <button class="btn btn-secondary btn-sm" @click="zoom_in">+</button>
                <button class="btn btn-secondary btn-sm" @click="zoom_out">-</button>

                <button class="btn btn-secondary btn-sm" @click="pan_left">
                    << /button>
                        <button class="btn btn-secondary btn-sm" @click="pan_right">></button>
            </div>

            <!-- Select form: Demand, Generation, Battery store, LDES -->
            <div class="btn-group" role="group" aria-label="Select view" style="float:right; margin-right:20px">
                <input type="radio" class="btn-check" name="btnradio" id="radio-demand" autocomplete="off" checked>
                <label class="btn btn-outline-primary btn-sm" for="radio-demand">Demand</label>

                <input type="radio" class="btn-check" name="btnradio" id="radio-generation" autocomplete="off">
                <label class="btn btn-outline-primary btn-sm" for="radio-generation">Generation</label>

                <input type="radio" class="btn-check" name="btnradio" id="radio-store1" autocomplete="off">
                <label class="btn btn-outline-primary btn-sm" for="radio-store1">Battery store</label>

                <input type="radio" class="btn-check" name="btnradio" id="radio-store2" autocomplete="off">
                <label class="btn btn-outline-primary btn-sm" for="radio-store2">LDES</label>
            </div>

            <p><b>Power view (GW):</b></p>

            <!-- A simple flot graph -->
            <div id="graph" style="width:100%;height:350px;"></div>
        </div>

        <div class="col mt-3">
            <p><b>Summary</b></p>

            <table class="table">
                <!-- Demand -->
                <tr>
                    <td>Demand</td>
                    <td>{{ demand_GWh*0.001 | toFixed(1)}} TWh</td>
                </tr>
                <tr>
                    <td>Supply</td>
                    <td>{{ supply_GWh*0.001 | toFixed(1)}} TWh</td>
                </tr>
                <tr>
                    <td>Oversupply factor</td>
                    <td v-if="demand_GWh">{{ supply_GWh/demand_GWh | toFixed(2) }}</td>
                </tr>
                <tr>
                    <td>Curtailment</td>
                    <td>{{ balance.surplus*0.001 | toFixed(1)}} TWh <span v-if="supply_GWh">({{ 100*balance.surplus/supply_GWh | toFixed(0) }}%)</span></td>
                </tr>
                <tr>
                    <td>Unmet demand</td>
                    <td>{{ balance.unmet*0.001 | toFixed(1)}} TWh</td>
                </tr>
                <tr></tr>
                <td>Demand supplied directly before storage</td>
                <td>{{ balance.before_store1*100 | toFixed(0)}} %</td>
                </tr>
                <tr>
                    <td>Balance after battery storage</td>
                    <td>{{ balance.after_store1*100 | toFixed(0)}} %</td>
                </tr>
                <tr>
                    <td>Balance after LDES</td>
                    <td>{{ balance.after_store2*100 | toFixed(3)}} %</td>
                </tr>
            </table>
        </div>
    </div>


    <hr>



    <div class="row">

        <div class="col-lg-3" style="background-color: whitesmoke">
            <p class="mt-3"><b>Demand:</b></p>
            <div class="row">
                <label class="form-label col-sm-6 col-form-label">Present day grid demand</label>
                <div class="col-sm-6">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" v-model.number="standard_demand_TWh" @change="update">
                        <span class="input-group-text">TWh/yr</span>
                    </div>
                </div>
            </div>

            <div class="row">
                <label class="form-label col-sm-6 col-form-label">Heat pump households</label>
                <div class="col-sm-6">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" v-model.number="heatpump_households" @change="update">
                        <span class="input-group-text">million</span>
                    </div>
                </div>
            </div>
        </div>


        <div class="col">

            <table class="table mt-3">
                <tr>
                    <th>Supply</th>
                    <th>% of demand</th>
                    <th>Annual generation</th>
                    <th>Capacity factor</th>
                    <th>Capacity</th>
                </tr>
                <tr>
                    <td>
                        <div style="margin-top:5px">Solar</div>
                    </td>
                    <td>
                        <div class="input-group">
                            <input type="text" class="form-control" v-model.number="solar_prc_of_demand" @change="update">
                            <span class="input-group-text">%</span>
                        </div>
                    </td>
                    <td>
                        <div class="input-group">
                            <input type="text" class="form-control" :value="solar_GWh*0.001 | toFixed(1)" disabled>
                            <span class="input-group-text">TWh</span>
                        </div>
                    </td>
                    <td>
                        <div class="input-group">
                            <input type="text" class="form-control" v-model.number="solar_GWh_per_GWp" @change="update">
                            <span class="input-group-text">GWh/GWp</span>
                        </div>
                    </td>
                    <td>
                        <div class="input-group">
                            <input type="text" class="form-control" :value="solar_GWp | toFixed(1)" disabled>
                            <span class="input-group-text">GWp</span>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td>
                        <div style="margin-top:5px">Wind</div>
                    </td>
                    <td>
                        <div class="input-group">
                            <input type="text" class="form-control" v-model.number="wind_prc_of_demand" @change="update">
                            <span class="input-group-text">%</span>
                        </div>
                    </td>
                    <td>
                        <div class="input-group">
                            <input type="text" class="form-control" :value="wind_GWh*0.001 | toFixed(1)" disabled>
                            <span class="input-group-text">TWh</span>
                        </div>
                    </td>
                    <td>
                        <div class="input-group">
                            <input type="text" class="form-control" v-model.number="wind_cap_factor" @change="update">
                            <span class="input-group-text">%</span>
                        </div>
                    </td>
                    <td>
                        <div class="input-group">
                            <input type="text" class="form-control" :value="wind_GWp | toFixed(1)" disabled>
                            <span class="input-group-text">GWp</span>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td>
                        <div style="margin-top:5px">Nuclear</div>
                    </td>
                    <td>
                        <div class="input-group">
                            <input type="text" class="form-control" v-model.number="nuclear_prc_of_demand" @change="update">
                            <span class="input-group-text">%</span>
                        </div>
                    </td>
                    <td>
                        <div class="input-group">
                            <input type="text" class="form-control" :value="nuclear_GWh*0.001 | toFixed(1)" disabled>
                            <span class="input-group-text">TWh</span>
                        </div>
                    </td>
                    <td>
                        <div class="input-group">
                            <input type="text" class="form-control" v-model.number="nuclear_cap_factor" @change="update">
                            <span class="input-group-text">%</span>
                        </div>
                    </td>
                    <td>
                        <div class="input-group">
                            <input type="text" class="form-control" :value="nuclear_GWp | toFixed(1)" disabled>
                            <span class="input-group-text">GWp</span>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="col-lg-2" style="background-color: whitesmoke">
            <p class="mt-3"><b>Battery storage:</b></p>
            <div class="row">
                <label class="form-label col-sm-6 col-form-label">Capacity</label>
                <div class="col-sm-6">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" v-model.number="store1.capacity" @change="update">
                        <span class="input-group-text">GWh</span>
                    </div>
                </div>
            </div>
            <div class="row">
                <label class="form-label col-sm-6 col-form-label">Round trip efficiency</label>
                <div class="col-sm-6">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" v-model.number="store1.round_trip_efficiency" @change="update">
                        <span class="input-group-text">%</span>
                    </div>
                </div>
            </div>
            <div class="row">
                <label class="form-label col-sm-6 col-form-label">Max charge & discharge rate</label>
                <div class="col-sm-6">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" v-model.number="store1.charge_max" @change="update">
                        <span class="input-group-text">GW</span>
                    </div>
                </div>
            </div>
            <div class="row">
                <label class="form-label col-sm-6 col-form-label">Cycles/year</label>
                <div class="col-sm-6">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" :value="store1.cycles | toFixed(0)" disabled>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2">
            <p class="mt-3"><b>Long duration energy store</b></p>
            <div class="row">
                <label class="form-label col-sm-6 col-form-label">Capacity</label>
                <div class="col-sm-6">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" v-model.number="store2.capacity" @change="update">
                        <span class="input-group-text">GWh</span>
                    </div>
                </div>
            </div>
            <div class="row">
                <label class="form-label col-sm-6 col-form-label">Charge efficiency</label>
                <div class="col-sm-6">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" v-model.number="store2.charge_efficiency" @change="update">
                        <span class="input-group-text">%</span>
                    </div>
                </div>
            </div>
            <div class="row">
                <label class="form-label col-sm-6 col-form-label">Discharge efficiency</label>
                <div class="col-sm-6">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" v-model.number="store2.discharge_efficiency" @change="update">
                        <span class="input-group-text">%</span>
                    </div>
                </div>
            </div>
            <div class="row">
                <label class="form-label col-sm-6 col-form-label">Max charge rate</label>
                <div class="col-sm-6">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" v-model.number="store2.charge_max" @change="update">
                        <span class="input-group-text">GW</span>
                    </div>
                </div>
            </div>
            <div class="row">
                <label class="form-label col-sm-6 col-form-label">Max discharge rate</label>
                <div class="col-sm-6">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" v-model.number="store2.discharge_max" @change="update">
                        <span class="input-group-text">GW</span>
                    </div>
                </div>
            </div>
            <div class="row">
                <label class="form-label col-sm-6 col-form-label">Cycles/year</label>
                <div class="col-sm-6">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" :value="store2.cycles | toFixed(1)" disabled>
                    </div>
                </div>
            </div>
            <p><i>E.g Hydrogen, e-Methanol e-Methane</i></p>
        </div>
    </div>

</div>

<script src="<?php echo $path; ?>ukgridsim.js?v=1"></script>