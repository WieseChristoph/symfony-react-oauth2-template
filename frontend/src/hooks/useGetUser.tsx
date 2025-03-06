import type User from "@/types/User";
import apiRequest from "@/utils/apiRequest";
import { useQuery } from "@tanstack/react-query";

export const GET_USER_QUERY_KEY = "getUser";

async function fetchUser() {
	return apiRequest("/users/me", "GET");
}

function useGetUser() {
	return useQuery<User, Error>({
		queryKey: [GET_USER_QUERY_KEY],
		queryFn: fetchUser,
		refetchOnWindowFocus: false,
	});
}

export default useGetUser;
