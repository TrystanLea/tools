<script src="https://cdn.jsdelivr.net/npm/vue@2"></script>
<script src="<?php echo $path_lib; ?>ecodan.js"></script>

<div class="container" style="max-width:800px" id="app">
    <div class="row">
        <div class="col">
            <br>
            <h3>Pipe pressure loss calculator</h3>
            <p>Calculates pressure loss using the Darcy Weisbach equation with the friction factor derived using the Newton-Raphson method to solve the Colebrook-White equation.</p>
        </div>
    </div>
    <hr>

    <div class="row">
        <div class="col">
            <label class="form-label">Pipe external diameter</label>
            <div class="input-group mb-3">
                <select class="form-control" v-model.number="pipe_external_diameter" @change="update">
                    <option value="15">15 mm</option>
                    <option value="22">22 mm</option>
                    <option value="28">28 mm</option>
                    <option value="35">35 mm</option>
                </select>
                <!--<span class="input-group-text">mm</span>-->
            </div>
        </div>

        <div class="col">
            <label class="form-label">Pipe length</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="pipe_length" @change="update">
                <span class="input-group-text">m</span>
            </div>
        </div>

        <div class="col">
            <label class="form-label">Flow rate</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="flow_rate" @change="update">
                <span class="input-group-text">m3/hr</span>
            </div>
        </div>

    </div>

    <br>
    <div class="row">
        <div class="col">
            <label class="form-label">Velocity</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" :value="velocity | toFixed(1)" @change="update" disabled>
                <span class="input-group-text">m/s</span>
            </div>
        </div>

        <div class="col">
            <label class="form-label">Reynolds Number</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" :value="reynoldsNumber | toFixed(0)" @change="update" disabled>
            </div>
        </div>

        <div class="col">
            <label class="form-label">Friction factor</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" :value="friction_factor | toFixed(6)" @change="update" disabled>
            </div>
        </div>

    </div>
    <br>

    <br>
    <div class="row">
        <div class="col">
            <label class="form-label">Temperature</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="temperature" @change="update">
                <span class="input-group-text">C</span>
            </div>
        </div>

        <div class="col">
            <label class="form-label">Dynamic Viscosity</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" :value="viscosity*1000 | toFixed(3)" @change="update" disabled>
                <span class="input-group-text">mPa.s</span>
            </div>
        </div>
    </div>
    <br>

    <div class="row">
        <div class="col">
            <label class="form-label">Pressure drop (kPa)</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" :value="pressure_drop_kPa | toFixed(3)" @change="update" disabled>
                <span class="input-group-text">kPa</span>
            </div>
        </div>
        <div class="col">
            <label class="form-label">Pressure drop (bar)</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" :value="pressure_drop_bar | toFixed(4)" @change="update" disabled>
                <span class="input-group-text">bar</span>
            </div>
        </div>
        <div class="col">
            <label class="form-label">Pressure drop (meters of head)</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" :value="pressure_drop_head | toFixed(4)" @change="update" disabled>
                <span class="input-group-text">m</span>
            </div>
        </div>
    </div>

    <br>
    <div class="row">
        <div class="col">

            <p>[1] Darcy weisbach equation <a href="https://www.engineeringtoolbox.com/darcy-weisbach-equation-d_646.html">https://www.engineeringtoolbox.com/darcy-weisbach-equation-d_646.html</a></p>

        </div>
    </div>
</div>

<script src="<?php echo $path; ?>pressure_loss.js?v=1"></script>

<script>
    var app = new Vue({
        el: '#app',
        data: {
            pipe_length: 1,
            pipe_external_diameter: 22,
            pipe_internal_diameter: 20.2 * 0.001,
            pipe_roughness: 0.0015 * 0.001,

            flow_rate: 3,
            velocity: null,
            reynoldsNumber: null,
            friction_factor: null,

            temperature: 35,
            viscosity: null,
            density: null,

            pressure_drop_kPa: null,
            pressure_drop_bar: null,
            pressure_drop_head: null
        },
        methods: {
            update: function() {

                if (app.pipe_external_diameter == 15) {
                    app.pipe_internal_diameter = 13.6 * 0.001;
                } else if (app.pipe_external_diameter == 22) {
                    app.pipe_internal_diameter = 20.2 * 0.001;
                } else if (app.pipe_external_diameter == 28) {
                    app.pipe_internal_diameter = 26.2 * 0.001;
                } else if (app.pipe_external_diameter == 35) {
                    app.pipe_internal_diameter = 32.6 * 0.001;
                }

                this.density = 998.206;

                this.velocity = calculateVelocity(this.flow_rate, this.pipe_internal_diameter);
                console.log("Velocity: " + this.velocity)

                // Calculate viscosity
                // https://en.wikipedia.org/wiki/Viscosity
                // this.viscosity = 2.414 * Math.pow(10,-5) * Math.pow(10, 247.8 / (this.temperature + 273.15 - 140))
                this.viscosity = 2.939 * Math.pow(10, -5) * Math.exp(507.88 / (this.temperature + 273.15 - 149.3))

                this.reynoldsNumber = calculateReynoldsNumber(this.density, this.velocity, this.pipe_internal_diameter, this.viscosity);
                console.log("The Reynolds number is: " + this.reynoldsNumber);

                // f = calculateFrictionFactorSJ(0.0015,20.2,reynoldsNumber)
                // console.log(f)

                this.friction_factor = calculateFrictionFactorNR(this.pipe_roughness, this.pipe_internal_diameter, this.reynoldsNumber)
                console.log("Newton-Raphson friction factor: " + this.friction_factor)

                // Calculate the pressure drop
                let deltaP = calculatePressureDrop(this.friction_factor, this.pipe_length, this.pipe_internal_diameter, this.density, this.velocity);
                this.pressure_drop_kPa = deltaP * 0.001
                this.pressure_drop_bar = this.pressure_drop_kPa * 0.01
                this.pressure_drop_head = this.pressure_drop_bar * 10.2

                console.log("The pressure drop in the pipe is: " + this.pressure_drop_bar + " bar");
                console.log("The pressure drop in the pipe is: " + this.pressure_drop_head + " m");
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