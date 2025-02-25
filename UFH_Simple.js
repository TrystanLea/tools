// The conduction shape factor for thermal calculation between
// outside surface of water pipe and surface of the infinity plane


// S = 2*pi*L / ln(8z/pi*D)

// https://www.engineersedge.com/heat_transfer/conductive_heat_transfer_parallel_cylinders_13836.htm
// Equation almost like this apart from a small deviation in cB

L = 1.0         // length of water pipe
M = 0.2         // pipe spacing  
D_o = 0.016     // pipe outside diameter
D_i = 0.014     // pipe inside diameter

// Difference between outside surface of water pipe and surface of the infinity plane
D_1 = 0.02 // where d_1 > (D_o / 2)


// Screed depth is pipe diameter + d_1
d_scr = D_o + D_1

cA = (2*M)/(Math.PI*D_o)
cB = (Math.PI*(D_1+(D_o/2))) / M
S_o = (2*Math.PI*L) / Math.log(cA*Math.sinh(cB))

// Q = Sk(T1-T2)


lambda_scr = 1.2

S = 0.5 * S_o

// Equivalent thermal resistance
R_eq = (M*L)/(S*lambda_scr)

t_w = 32.5
t_op = 20

alpha_u = 5.7 // revisit!!
//d_co    = ??
//h_co    = ??
h_p     = ??

// Convective heat exchange coefficient between inside of pipe and water
Re = (flow_rate * D_o) / (A*u)     // Reynolds
Pr = ??                            // Prandtl
L = ??                                  
a_w = 0.116 * (Math.pow(Re,2/3)-125)*Math.pow(Pr,1/3)*(1+Math.pow(D_o/L,2/3))*(h_p/D_o)


R = 1 / alpha_u
// R += d_co / h_co                             // covering
R += (M * Math.log(D_o/D_i)) / (2*Math.PI*h_p)
R += M / (Math.PI*a_w*D_i)
R += R_eq


Q_u = (t_w-t_op) / R

console.log(Q_u)


t_u = (Q_u / alpha_u) + t_op
