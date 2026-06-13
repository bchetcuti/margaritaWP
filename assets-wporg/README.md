# WordPress.org asset plan

This directory is a repository planning area for WordPress.org listing artwork. WordPress.org SVN expects public listing assets in a top-level `assets` directory beside `trunk`, not inside the plugin package that is uploaded or copied into `trunk`.

Expected SVN layout:

```text
margarita-measurements/
├── assets/
│   ├── banner-772x250.png
│   ├── banner-1544x500.png
│   ├── icon-128x128.png
│   ├── icon-256x256.png
│   ├── screenshot-1.png
│   ├── screenshot-2.png
│   ├── screenshot-3.png
│   ├── screenshot-4.png
│   ├── screenshot-5.png
│   ├── screenshot-6.png
│   ├── screenshot-7.png
│   ├── screenshot-8.png
│   ├── screenshot-9.png
│   └── screenshot-10.png
└── trunk/
    └── plugin files
```

## Required artwork decisions

The final WordPress.org submission should include owner-approved artwork. Do not generate placeholder binary image files for missing assets.

## Expected files

- `assets/banner-772x250.png`
- `assets/banner-1544x500.png`
- `assets/icon-128x128.png`
- `assets/icon-256x256.png`
- `assets/screenshot-1.png`
- `assets/screenshot-2.png`
- `assets/screenshot-3.png`
- `assets/screenshot-4.png`
- `assets/screenshot-5.png`
- `assets/screenshot-6.png`
- `assets/screenshot-7.png`
- `assets/screenshot-8.png`
- `assets/screenshot-9.png`
- `assets/screenshot-10.png`

## Current repository status

This repository currently includes planning/source copies under `assets-wporg/`. At release time, copy only final, owner-approved files into the WordPress.org SVN `assets/` directory. Do not describe screenshots as complete in the WordPress.org listing unless the corresponding final image files actually exist in SVN.

Currently present in this repository:

- `assets-wporg/banner-772x250.png`
- `assets-wporg/banner-1544x500.png`
- `assets-wporg/icon-128x128.png`
- `assets-wporg/icon-256x256.png`
- `assets-wporg/screenshot-1.png`
- `assets-wporg/screenshot-2.png`
- `assets-wporg/screenshot-3.png`
- `assets-wporg/screenshot-4.png`

Still requiring final owner-provided artwork before submission:

- `screenshot-5.png`
- `screenshot-6.png`
- `screenshot-7.png`
- `screenshot-8.png`
- `screenshot-9.png`
- `screenshot-10.png`

Do not add screenshots for unimplemented visitor account, sharing, analytics, remote-service, or saved-recipe features unless those features are implemented in a later release.
