import useAuth from "@/hooks/useAuth";

import DiscordLoginButton from "@/components/DiscordLoginButton";
import GoogleLoginButton from "@/components/GoogleLoginButton";
import LogoutButton from "@/components/LogoutButton";
import UserProfile from "@/components/UserProfile";

const Home: React.FC = () => {
	const { isAuthenticated, isLoading, user } = useAuth();

	return (
		<div className="flex flex-col items-center gap-2 text-white">
			<h1 className="text-6xl">Hello World</h1>
			{isLoading ? (
				<p>Loading...</p>
			) : isAuthenticated && user ? (
				<>
					<UserProfile user={user} />
					<LogoutButton />
				</>
			) : (
				<>
					<GoogleLoginButton />
					<DiscordLoginButton />
				</>
			)}
		</div>
	);
};

export default Home;
