// Underfloor heating calculation based on EN 1264
// Not quite correct but mostly here

v_V = 42.5           // flow temperature
v_R = 37.5           // return temperature
v_i = 20           // room temperature

T =   0.15         // Pipe spacing (0.05m <= T <= 0.375m)
s_u = 0.03         // Thickness of layer above pipe (>= 0.01m)
D =   0.016        // Pipe external diameter (0.008m <= D <= 0.03m)

lambda_E   = 1.2   // thermal conductivity of screed (W/mK)
R_lambda_B = 0.15   // thermal conduction resistance of floor covering (m2.K/W)

// ---------------------------------------------------------------------------

v_H = (v_V - v_R) / Math.log((v_V-v_i)/(v_R-v_i))

// B = Bo 
// for a pipe conductivity lambda_R = 0.35 W/m2K
// for a pipe thickness s_R = 0.002 m
B =   6.7

// a_B
alpha      = 10.8  // W/m2.K
lambda_u0  = 1.0   // W/m.K
s_u0       = 0.045 // m

a_B = ((1/alpha)+(s_u0/lambda_u0))/((1/alpha)+(s_u0/lambda_E)+R_lambda_B)


// spacing factor Table A.1 converted to equation
a_T = 2.0 * Math.pow(R_lambda_B,2) - (0.94*R_lambda_B) + 1.23              
a_u = 1.04 // 1.057                 // covering factor Table A.2
a_D = 1.03 // 1.04                  // pipe ext diameter factor Table A.3


m_T = 1 - (T/0.075)         // (6)
m_u = 100 * (0.045-s_u)     // (7)
m_D = 250 * (D - 0.020)     // (8)

K_H = B * a_B * Math.pow(a_T,m_T) * Math.pow(a_u,m_u) * Math.pow(a_D,m_D)

q = K_H * v_H

console.log(q)
