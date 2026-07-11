// Smoke fixture for node/tsconfig.base.json — proves it loads and that strict mode is applied.
export const a8cspConfigSmoke: number = 1;

// @ts-expect-error only compiles under strictNullChecks; dropping `strict` from the base fails the smoke.
export const a8cspStrictProbe: string = null;
