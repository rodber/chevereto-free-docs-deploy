# chevere

> The official documentation site for Chevere. 

## Development

```bash
yarn dev
yarn build
```

## Formatting

| Path condition id | `README.md` | Folders | Navbar as      | Sidebar as                            |
| ----------------- | ----------- | ------- | -------------- | ------------------------------------- |
| @1                | No          | No      | Dropdown       | Auto for each single page             |
| @2                | No          | 1       | Same as @1 (*) | Same as @1                            |
| @3                | Yes         | No      | Link           | Sidebar with children `['', <page>]`  |
| @4                | Yes         | 1       | Same as @3     | A combined version @3 for each folder |

**(*)** Case `@2` is **discouraged** (needs to implement nav groups)