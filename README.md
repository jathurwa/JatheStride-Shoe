# JatheStride Shoe Store

CodeIgniter 4 shoe catalog deployed to Vercel, using MySQL-compatible TiDB Cloud
for product data and Cloudinary for persistent product images.

## Production environment

Set these variables in the Vercel project settings for every environment that
should access the production store:

| Variable | Required | Description |
| --- | --- | --- |
| `CI_ENVIRONMENT` | Yes | Set to `production`. |
| `DB_HOST` | Yes | TiDB Cloud endpoint hostname. |
| `DB_NAME` | Yes | TiDB database/schema name. |
| `DB_USER` | Yes | TiDB database username. |
| `DB_PASSWORD` | Yes | TiDB database password. |
| `DB_PORT` | No | Defaults to `4000` for TiDB Cloud, otherwise MySQL's `3306`. |
| `DB_SSL` | No | Set to `true` for TLS, or leave unset for TiDB Cloud's automatic TLS configuration. |
| `CLOUDINARY_CLOUD_NAME` | Required for file uploads | Cloudinary cloud name. |
| `CLOUDINARY_API_KEY` and `CLOUDINARY_API_SECRET` | One option for uploads | Use both for signed uploads. |
| `CLOUDINARY_UPLOAD_PRESET` | Alternative upload option | Use an unsigned preset instead of the API key and secret. Configure the preset's upload folder in Cloudinary. |
| `WHATSAPP_PHONE_NUMBER` | No | Store contact number; defaults to the project number. |
| `WHATSAPP_COUNTRY_CODE` | No | Country calling code for a local number; defaults to `254`. |

Never put database or Cloudinary secrets in source control. After changing
Vercel environment variables, redeploy so the function receives them.

## Database setup

For a new database, apply `app/Database/scheme.sql`. For an existing
CodeIgniter-managed database, run the migrations using the production database
environment values:

```powershell
php spark migrate
```

The migration adds the Cloudinary public ID field to existing `shoes` tables;
the size-stock table is also required for product detail and inventory
management.

## Image uploads

On Vercel, uploaded files are sent directly to Cloudinary and are not stored on
the serverless filesystem. Configure `CLOUDINARY_CLOUD_NAME` and either both
signed API credentials or an unsigned upload preset. Without Cloudinary, admins
can save a product by providing a direct HTTP/HTTPS image URL. Local development
can use local disk uploads.

## Store and admin flow

The storefront routes are `/`, `/product/{slug}`, `/cart`, and `/checkout`.
Customers select a size, add the shoe to the bag, and submit contact details to
continue the order in WhatsApp. The admin portal is available at `/admin`;
product creation and edits require an authenticated admin session.