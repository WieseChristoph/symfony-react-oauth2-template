import {
	createRootRoute,
	createRoute,
	createRouter,
} from "@tanstack/react-router";

import Home from "./Home";
import Root from "./Root";

const rootRoute = createRootRoute({
	component: Root,
});

const homeRoute = createRoute({
	getParentRoute: () => rootRoute,
	path: "/",
	component: Home,
});

const routeTree = rootRoute.addChildren([homeRoute]);

export default createRouter({ routeTree });
