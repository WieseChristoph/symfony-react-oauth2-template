import useGetUser from "@/hooks/useGetUser";
import type User from "@/types/User";
import { type ReactNode, createContext, useEffect, useState } from "react";

interface AuthContextType {
	isAuthenticated: boolean;
	isLoading: boolean;
	user?: User;
}

export const AuthContext = createContext<AuthContextType>({
	isAuthenticated: false,
	isLoading: true,
	user: undefined,
});

const AuthProvider: React.FC<{ children: ReactNode }> = ({ children }) => {
	const [isAuthenticated, setAuthenticated] = useState<boolean>(false);
	const [user, setUser] = useState<User | undefined>(undefined);

	const { data, isLoading, isSuccess } = useGetUser();

	useEffect(() => {
		if (!isSuccess || !data) {
			setAuthenticated(false);
			return;
		}

		setAuthenticated(true);
		setUser(data);
	}, [data, isSuccess]);

	return (
		<AuthContext.Provider value={{ isAuthenticated, isLoading, user }}>
			{children}
		</AuthContext.Provider>
	);
};

export default AuthProvider;
