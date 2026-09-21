// Smoke fixture for node/tsconfig.base.json — proves it loads and that strict type checking applies.
export const a8cspConfigSmoke: number = 1;

// @ts-expect-error only compiles under strictNullChecks, which TypeScript enables by default; a base that turned it off fails the smoke.
export const a8cspStrictProbe: string = null;
