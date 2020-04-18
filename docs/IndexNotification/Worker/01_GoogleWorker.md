# Google Index Worker
Update / Delete data in realtime via google index API.

## Configuration 

| Name | Default | Description
|------|---------|------------|
| `push_requests_per_day` | 200 |Set allowed push requests per day. This check is currently not available. |
| `push_requests_per_minute` | 600 | Set allowed push requests per minute. This check is currently not available. |
| `auth_config` | null | Set path to your auth config (mostly `app/config/pimcore/google-api-private-key.json`. |
