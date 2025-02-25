<script src="https://cdn.jsdelivr.net/npm/vue@2"></script>
<script src="<?php echo $path_lib; ?>ecodan.js"></script>

<div class="container mt-3" style="max-width:800px" id="app">
    <div class="row">
        <div class="col">
            <h3>Heat pump & heat exchanger calculator</h3>
            <p>Calculate heat output and COP for a system with a counterflow plate heat exchanger between the heat pump and the radiator system. Calculation based on effectiveness NTU method [1].</p>
        </div>
    </div>
    <hr>

    <div class="row">
        <div class="col">
            <label class="form-label">Room temperature</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="room" @change="update">
                <span class="input-group-text">°C</span>
            </div>
        </div>
        <div class="col">
            <label class="form-label">Outside temperature</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="outside" @change="update">
                <span class="input-group-text">°C</span>
            </div>
        </div>
        <div class="col">
            <label class="form-label">Heat output</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="heatpump_output" @change="update" style="background-color:#fed">
                <span class="input-group-text">W</span>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <label class="form-label">Heat exchanger heat transfer rate</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="htc" @change="update">
                <span class="input-group-text">W/m2.K</span>
            </div>
        </div>
        <div class="col">
            <label class="form-label">Heat exchanger Area</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="area" @change="update">
                <span class="input-group-text">m2</span>
            </div>
        </div>
    </div>
    <br>

    <div class="row">
        <div class="col">
            <table class="table">
                <tr>
                    <th></th>
                    <th max-width="250px">Heat pump side (Primary)</th>
                    <th max-width="250px">Radiator side (Secondary)</th>
                </tr>
                <tr>
                    <td>Flow rate</td>
                    <td>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" v-model.number="mh_lmin" @change="update">
                            <span class="input-group-text">L/min</span>
                        </div>
                    </td>
                    <td>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" v-model.number="mc_lmin" @change="update">
                            <span class="input-group-text">L/min</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>Heat capacity</td>
                    <td>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" v-model.number="Cph" @change="update">
                            <span class="input-group-text">J/kg.K</span>
                        </div>
                    </td>
                    <td>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" v-model.number="Cpc" @change="update">
                            <span class="input-group-text">J/kg.K</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>Flow temperature</td>
                    <td>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" :value="Thi | toFixed(1)" disabled>
                            <span class="input-group-text">°C</span>
                        </div>
                    </td>
                    <td>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" :value="Tco | toFixed(1)" disabled>
                            <span class="input-group-text">°C</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>Return temperature</td>
                    <td>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" :value="Tho | toFixed(1)" disabled>
                            <span class="input-group-text">°C</span>
                        </div>
                    </td>
                    <td>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" :value="Tci | toFixed(1)" disabled>
                            <span class="input-group-text">°C</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>Mean water temperature</td>
                    <td>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" :value="(Thi+Tho)*0.5 | toFixed(1)" disabled>
                            <span class="input-group-text">°C</span>
                        </div>
                    </td>
                    <td>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" :value="(Tci+Tco)*0.5 | toFixed(1)" disabled>
                            <span class="input-group-text">°C</span>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <label class="form-label">Rated radiator output [2]</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="rated_output" @change="update">
                <span class="input-group-text">W</span>
            </div>
        </div>
        <div class="col">
            <label class="form-label">@ Rated deltaT (MWT - Room)</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model="rated_dT" disabled>
                <span class="input-group-text">°K</span>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <label class="form-label">COP Method</label>
            <div class="input-group mb-3">
                <select class="form-control" v-model="cop_method" @change="update">
                    <option value="ecodan">5kW EcoDan Datasheet</option>
                    <option value="carnot">Carnot equation [3]</option>
                </select>
            </div>
        </div>
        <div class="col">
            <label class="form-label">Heat pump COP</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" :value="heatpump_COP | toFixed(2)" disabled>
            </div>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col">
            <p><b>Comparison with open-circuit system without a heat exchanger:</b></p>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <label class="form-label">Heat pump COP if driving radiators directly without the heat exchanger (Flow: {{ app.Tco | toFixed(1) }}°C, Return: {{ app.Tci | toFixed(1) }}°C):</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" :value="heatpump_COP_compare | toFixed(2)" disabled>
            </div>
        </div>
        <div class="col">
            <label class="form-label"><b>or</b> direct radiator output at the primary flow temperature: {{ app.Thi | toFixed(1) }}°C, return: {{ app.Tho_direct | toFixed(1) }}°C and COP: {{heatpump_COP | toFixed(2)}}</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" :value="radiator_output_compare | toFixed(0)" disabled>
                <span class="input-group-text">W</span>
            </div>
        </div>

    </div>
    <br>
    <div class="row">
        <div class="col">

            <p>[1] Effectiveness NTU method, implementation here ported from python <a href="https://github.com/CalebBell/ht/blob/master/ht/hx.py#L899">CalebBell/ht library.</a></p>

            <p>[2] 15000 W @ 50K = 7x 1200x600mm K2 double panel radiators, each with an output of 2145W @ 50K.</p>

            <p>[3] Carnot COP equation based on +4K offset on flow temperature, -6K offset on outside temperature and a practical efficiency factor of 50%.</p>
        </div>
    </div>
</div>
<script src="<?php echo $path; ?>hex2.js?v=1"></script>
