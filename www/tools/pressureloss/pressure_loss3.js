function findFlows(A, B, C, initialFlow1 = 1, initialFlow2 = 1, tolerance = 0.0001, maxIterations = 10000) {
    let flow1 = initialFlow1;
    let flow2 = initialFlow2;
    let iteration = 0;
    let dp1 = A * flow1 ** 2;
    let dp2 = B * flow2 ** 2;

    // Iterate until dp1 and dp2 are approximately equal or max iterations reached
    while (Math.abs(dp1 - dp2) > tolerance && iteration < maxIterations) {
        // Adjust flow rates based on the difference in dp
        if (dp1 > dp2) {
            flow1 -= tolerance;
            flow2 += tolerance;
        } else {
            flow1 += tolerance;
            flow2 -= tolerance;
        }

        // Recalculate dp1 and dp2
        dp1 = A * flow1 ** 2;
        dp2 = B * flow2 ** 2;

        iteration++;
    }

    // Calculate dp_s for the series section
    const flow3 = flow1 + flow2;
    const dp_s = C * flow3 ** 2;

    return {
        flow1: flow1,
        flow2: flow2,
        dp_s: dp_s,
        iterations: iteration,
        converged: iteration < maxIterations
    };
}

// Example usage
const A = 0.5;
const B = 0.5;
const C = 0.3;
const results = findFlows(A, B, C);
console.log(results);

