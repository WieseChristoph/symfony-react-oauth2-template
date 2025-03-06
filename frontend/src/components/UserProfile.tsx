import type User from "@/types/User";

const ProfileField: React.FC<{ label: string; value: string }> = ({
	label,
	value,
}) => (
	<p className="flex justify-between gap-5">
		<span className="font-bold">{label}</span>
		<span>{value}</span>
	</p>
);

const UserProfile: React.FC<{ user: User }> = ({ user }) => {
	return (
		<div className="flex items-center gap-3 border border-white p-3 text-white">
			<img
				src={user.avatarUrl}
				alt="Avatar"
				referrerPolicy="no-referrer"
			/>
			<div>
				<ProfileField label="ID" value={user.id.toString()} />
				<ProfileField label="Username" value={user.username} />
				<ProfileField label="E-Mail" value={user.email} />
				<ProfileField label="Roles" value={user.roles.join(", ")} />
				<ProfileField label="Created At" value={user.createdAt} />
			</div>
		</div>
	);
};

export default UserProfile;
