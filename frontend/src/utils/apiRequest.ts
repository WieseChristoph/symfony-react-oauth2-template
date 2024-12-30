async function apiRequest(
	path: string,
	method: "GET" | "POST" | "PUT" | "DELETE",
	body?: unknown,
) {
	const res = await fetch(path, {
		method,
		headers: { "Content-Type": "application/json" },
		credentials: "include",
		body: JSON.stringify(body),
	});

	if (!res.ok) {
		throw new Error(
			`Failed to fetch '${path}': ${res.status} ${res.statusText}`,
		);
	}

	return res.json();
}

export default apiRequest;
