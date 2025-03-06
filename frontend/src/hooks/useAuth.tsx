import { AuthContext } from "@/providers/AuthProvider";
import { useContext } from "react";

function useAuth() {
	const context = useContext(AuthContext);

	if (!context) {
		throw new Error("useAuth must be used within an AuthProvider");
	}

	return context;
}

export default useAuth;
