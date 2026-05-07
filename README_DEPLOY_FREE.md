# Free Deployment Setup

This project is Laravel + MongoDB and cannot be deployed on Vercel because Vercel does not support PHP runtime apps.

## What was added

- `Dockerfile` — builds the Laravel app with PHP 8.2, Apache, MongoDB PHP extension, and Composer.
- `.dockerignore` — prevents unnecessary files from being included in Docker builds.

## Recommended free hosts

### Render
1. Create a free account at https://render.com
2. Add a new Web Service
3. Connect your GitHub/GitLab repo
4. Choose `Docker` and use the repository root
5. Render will detect the `render.yaml` file in the project root and use it automatically
6. In Render dashboard, add these environment variables:
   - `APP_KEY` (generate a secure value)
   - `APP_URL=https://<your-render-url>`
   - `DB_CONNECTION=mongodb`
   - `DB_HOST`
   - `DB_PORT`
   - `DB_DATABASE`
   - `DB_USERNAME`
   - `DB_PASSWORD`
   - `MAIL_FROM_ADDRESS`
   - `MAIL_FROM_NAME`

### Fly.io
1. Install Fly CLI: https://fly.io/docs/hands-on/install-fly/
2. Run `fly launch` in the project root
3. Add MongoDB connection variables in Fly dashboard
4. Deploy with `fly deploy`

## Notes for MongoDB

This project already uses `mongodb/laravel-mongodb` via `composer.json`.
You should use a free MongoDB Atlas cluster or any existing MongoDB server.

## How to deploy locally for testing

1. Build the Docker image:
   ```bash
   docker build -t compliance-hrms .
   ```
2. Run the app:
   ```bash
   docker run -p 80:80 --env-file .env -d compliance-hrms
   ```

## Important

Do not use Heroku if you need fully free hosting without payment verification.
Use Render or Fly with the provided `Dockerfile` instead.
