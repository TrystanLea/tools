// Summary of activity levels

// Data from: Carbon dioxide generation rates for building occupants
// https://www.ncbi.nlm.nih.gov/pmc/articles/PMC5666301/

// The following levels are summarised from table 3
// MET levels are rounded to match C02 generation rate table
// in order to simplify lookup
// Exercise / Dancing reduce to 4.0 to match table should be 5-7.5

var activity_met = {
    "Sleeping": 0.95,
    "Sitting quietly": 1.2,
    "Sitting reading, writing, typing": 1.3,
    "Office work, light effort": 1.5,
    "Light cleaning": 2.3,
    "Child care": 2.5,
    "Standing tasks, light effort": 3.0,
    "Kitchen activity": 3.3,
    "Moderate cleaning": 3.8,
    "Exercise / Dancing": 4.0
}

var co2_production_rates = {

    // age: kg, BMR, MET: 1.0, 1.2, 1.4, 1.6, 2.0, 3.0, 4.0
    "male": {
        "0": [8, 1.86, 0.0009, 0.0011, 0.0013, 0.0014, 0.0018, 0.0027, 0.0036],
        "1": [12.8, 3.05, 0.0015, 0.0018, 0.0021, 0.0024, 0.003, 0.0044, 0.0059],
        "3": [18.8, 3.9, 0.0019, 0.0023, 0.0026, 0.003, 0.0038, 0.0057, 0.0075],
        "6": [31.9, 5.14, 0.0025, 0.003, 0.0035, 0.004, 0.005, 0.0075, 0.01],
        "11": [57.6, 7.02, 0.0034, 0.0041, 0.0048, 0.0054, 0.0068, 0.0102, 0.0136],
        "16": [77.3, 7.77, 0.0037, 0.0045, 0.0053, 0.006, 0.0075, 0.0113, 0.015],
        "21": [84.9, 8.24, 0.0039, 0.0048, 0.0056, 0.0064, 0.008, 0.012, 0.016],
        "30": [87, 7.83, 0.0037, 0.0046, 0.0053, 0.0061, 0.0076, 0.0114, 0.0152],
        "40": [90.5, 8, 0.0038, 0.0046, 0.0054, 0.0062, 0.0077, 0.0116, 0.0155],
        "50": [89.5, 7.95, 0.0038, 0.0046, 0.0054, 0.0062, 0.0077, 0.0116, 0.0154],
        "60": [89.5, 6.84, 0.0033, 0.004, 0.0046, 0.0053, 0.0066, 0.0099, 0.0133],
        "70": [83.9, 6.57, 0.0031, 0.0038, 0.0045, 0.0051, 0.0064, 0.0095, 0.0127],
        "80": [76.1, 6.19, 0.003, 0.0036, 0.0042, 0.0048, 0.006, 0.009, 0.012]
    },

    // age: kg, BMR, MET: 1.0, 1.2, 1.4, 1.6, 2.0, 3.0, 4.0
    "female": {
        "0": [7.7, 1.75, 0.0008, 0.001, 0.0012, 0.0014, 0.0017, 0.0025, 0.0034],
        "1": [12.3, 2.88, 0.0014, 0.0017, 0.002, 0.0022, 0.0028, 0.0042, 0.0056],
        "3": [18.3, 3.59, 0.0017, 0.0021, 0.0024, 0.0028, 0.0035, 0.0052, 0.007],
        "6": [31.7, 4.73, 0.0023, 0.0027, 0.0032, 0.0037, 0.0046, 0.0069, 0.0092],
        "11": [55.9, 6.03, 0.0029, 0.0035, 0.0041, 0.0047, 0.0058, 0.0088, 0.0117],
        "16": [65.9, 6.12, 0.0029, 0.0036, 0.0042, 0.0047, 0.0059, 0.0089, 0.0119],
        "21": [71.9, 6.49, 0.0031, 0.0038, 0.0044, 0.005, 0.0063, 0.0094, 0.0126],
        "30": [74.8, 6.08, 0.0029, 0.0035, 0.0041, 0.0047, 0.0059, 0.0088, 0.0118],
        "40": [77.1, 6.16, 0.0029, 0.0036, 0.0042, 0.0048, 0.006, 0.009, 0.0119],
        "50": [77.5, 6.17, 0.003, 0.0036, 0.0042, 0.0048, 0.006, 0.009, 0.012],
        "60": [76.8, 5.67, 0.0027, 0.0033, 0.0038, 0.0044, 0.0055, 0.0082, 0.011],
        "70": [70.8, 5.45, 0.0026, 0.0032, 0.0037, 0.0042, 0.0053, 0.0079, 0.0106],
        "80": [64.1, 5.19, 0.0025, 0.003, 0.0035, 0.004, 0.005, 0.0075, 0.0101]
    }
}

function interpolate(x, x1, y1, x2, y2) {
    // Linear interpolation formula
    return y1 + (x - x1) * (y2 - y1) / (x2 - x1);
}

function calculate_co2_rate(age, gender, activityLevel) {
    // Find the closest age category for the person
    const ageGroups = Object.keys(co2_production_rates[gender]).map(Number).sort((a, b) => a - b);
    let ageGroup = ageGroups[0];
    for (let i = 0; i < ageGroups.length; i++) {
        if (age >= ageGroups[i]) {
            ageGroup = ageGroups[i];
        } else {
            break;
        }
    }

    // Retrieve the CO2 production rates for the person's age group and gender
    const rates = co2_production_rates[gender][ageGroup.toString()];
    const metLevels = [1.0, 1.2, 1.4, 1.6, 2.0, 3.0, 4.0];
    const personMET = activity_met[activityLevel];

    // Find the nearest MET levels to interpolate between
    let lowerMETIndex = 0;
    for (let i = 0; i < metLevels.length; i++) {
        if (personMET >= metLevels[i]) {
            lowerMETIndex = i;
        } else {
            break;
        }
    }

    // If the personMET matches exactly, no need to interpolate
    if (metLevels[lowerMETIndex] === personMET) {
        return rates[lowerMETIndex + 2]; // +2 to skip age and BMR in the rates array
    } else {
        // Interpolate between the two nearest MET levels
        const lowerMET = metLevels[lowerMETIndex];
        const upperMET = metLevels[lowerMETIndex + 1];
        const lowerRate = rates[lowerMETIndex + 2]; // +2 to skip age and BMR in the rates array
        const upperRate = rates[lowerMETIndex + 3]; // +3 because we are moving to the next MET level
        return interpolate(personMET, lowerMET, lowerRate, upperMET, upperRate);
    }
}

/*

// Test example
var person = {
    "age": 36,
    "gender": "male",
    "activity": "Child care"
}
console.log(calculate_co2_rate(person.age, person.gender, person.activity));

*/
