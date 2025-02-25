function calculateVelocity(Q, D) {
    // Q: Flow rate in cubic meters per hour (m³/hr)
    // d: Diameter of the pipe in meters (m)
    
    // Convert Q from m³/hr to m³/s
    let Q_m3s = Q / 3600;
    
    // Calculate the cross-sectional area of the pipe
    let A = Math.PI * Math.pow(D / 2, 2);
    
    // Calculate the velocity
    let v = Q_m3s / A;
    
    return v; // Velocity in meters per second (m/s)
}

function calculateReynoldsNumber(density, velocity, diameter, viscosity) {    
    // https://en.wikipedia.org/wiki/Reynolds_number
    // density (kg/m3), velocity (m/s), diameter (m), viscosity (Pa·s or kg/(m·s))
    return (density * velocity * diameter) / viscosity;
}


function calculateFrictionFactorSJ(e, D, Re) {
		if (Re < 2000) {
			  return 64 / Re;
		} else {
		    // Swamee–Jain equation
		    // https://en.wikipedia.org/wiki/Darcy_friction_factor_formulae
			  return (0.25 / Math.pow(Math.log10(((e / D) / 3.7) + (5.74 / Math.pow(Re, 0.9))), 2));
    }
}

// Tkachenko, Mileikovskyi (this does not return consistent results with other methods?)
function calculateFrictionFactorTM(e, D, Re) {
    let A0 = -0.79638 * Math.log(((e / D) / 8.208)+(7.3357 / Re))
    let A1 = (Re * (e / D)) + 9.3120665 * A0

    // Advanced 
    return Math.pow((8.128943 + A1) / ((8.128943 * A0) - (0.86859209 * A1 * Math.log(A1 / 3.7099535 * Re))),2)
    
    // Simpler
    return Math.pow(0.8284 * Math.log(((e/D)/4.913)+(10.31/Re)),2);
}

function calculateFrictionFactorNR(e, D, reynoldsNumber) {
    // Initial guess for f using the Zigrang-Sylvester equation (a simpler approximation)
    let f = (0.782 * Math.log(reynoldsNumber) - 1.81)**-2;
  
    // Iteratively solve the Colebrook-White equation using the Newton-Raphson method
    for (let i = 0; i < 100; i++) {
        let fNew = 1.0 / ((-2.0 * Math.log10(((e / D) / 3.7) + (2.51 / (reynoldsNumber * Math.sqrt(f))))) ** 2);
        let df = fNew - f;
        if (Math.abs(df) < 1e-5) {
            break;
        }
        f = fNew;
    }
  
    return f;
}

// Darcy weisbach equation
// https://www.engineeringtoolbox.com/darcy-weisbach-equation-d_646.html
function calculatePressureDrop(frictionFactor, length, diameter, density, velocity) {
    // length (m), diameter (m), density (kg/m^3), velocity (m/s)
    // pressure drop (Pa)
    return frictionFactor * (length / diameter) * (density * velocity * velocity / 2);
}
