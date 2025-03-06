export enum Role {
	ADMIN = "ROLE_ADMIN",
	USER = "ROLE_USER",
}

interface User {
	id: number;
	username: string;
	email: string;
	avatarUrl: string;
	roles: Role[];
	createdAt: string;
}

export default User;
