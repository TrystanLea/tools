
var app = new Vue({
    el: '#app',
    data: {
        capex: 1500,
        opex: 43,
        months_to_build: 48,
        lifespan: 30,
        interest_rate: 6.3,
        capacity_factor: 62,
        lcoe: 0
    },
    methods: {
        update: function () {
            var annual_cost = app.annual_cost(this.capex, this.opex, this.fuel, this.months_to_build, this.lifespan, this.interest_rate*0.01)

            var annual_generation = this.capacity_factor * 0.01 * 24 * 365;
            app.lcoe = 1000 * annual_cost / annual_generation;
        },
        annual_cost: function (capex, opex, fuel, months_to_build, lifespan, interest_rate) {

            principal_at_commisioning = capex * Math.pow((1 + interest_rate), months_to_build / 12)

            lifespan_months = lifespan * 12

            monthly_payment = (interest_rate / 12) * (1 / (1 - (1 + interest_rate / 12) ** (-lifespan_months))) * principal_at_commisioning
            annual_payment = monthly_payment * 12

            return annual_payment + opex
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

app.update();
