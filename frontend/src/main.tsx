import { StrictMode } from "react";
import { createRoot } from "react-dom/client";
import "./index.css";
import router from "@/routes/router";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { RouterProvider } from "@tanstack/react-router";
import AuthProvider from "./providers/AuthProvider";

const rootElement = document.getElementById("root");
if (!rootElement) throw new Error("Root element not found!");

const queryClient = new QueryClient({
	defaultOptions: { queries: { retry: false } },
});

createRoot(rootElement).render(
	<StrictMode>
		<QueryClientProvider client={queryClient}>
			<AuthProvider>
				<RouterProvider router={router} />
			</AuthProvider>
		</QueryClientProvider>
	</StrictMode>,
);
