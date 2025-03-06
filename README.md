# Symfony React OAuth2 Template

This is a simple full stack application that uses a Symfony backend with OAuth2 to authenticate users with Discord and Google. The frontend is a React application built with Vite.

## Environment Variables

Copy the `.env.example` file to `.env` and fill in the values.

### General

- `APP_ENV` - Environment for the Symfony backend. Allowed values are `dev`, `prod` and `test`
- `APP_SECRET` - Random cryptographic string
- `APP_URL` - The absolute URL to the application (used for OAuth2 redirect)

> [!IMPORTANT]
> The backend and frontend should run unter the same domain to work properly out of the box

### Database

- `DATABASE_PASSWORD` - Database password

### OAuth2

- `OAUTH_GOOGLE_CLIENT_ID` - Google OAuth2 client ID
- `OAUTH_GOOGLE_CLIENT_SECRET` - Google OAuth2 client secret
- `OAUTH_DISCORD_CLIENT_ID` - Discord OAuth2 client ID
- `OAUTH_DISCORD_CLIENT_SECRET` - Discord OAuth2 client secret

> [!IMPORTANT]
> When creating OAuth2 credentials, add `https://your-domain.com/api/auth/<google/discord>/callback` as the redirect url for the specific provider

## Development

To start the development environment, run the following command in the root directory of this template after setting the environment variables:

```bash
docker compose up
```

The Application will be available at `http://localhost:8080`.