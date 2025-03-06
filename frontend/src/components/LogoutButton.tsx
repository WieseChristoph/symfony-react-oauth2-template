const LogoutButton: React.FC = () => {
	return (
		<a
			className="rounded-sm bg-red-900 px-3 py-2 font-bold"
			href="/api/auth/logout"
		>
			Logout
		</a>
	);
};

export default LogoutButton;
