# Themes

Themes are discovered from the host application's themes/ directory, followed
by the package's open-source themes and bundled vendor themes. The first
matching slug wins, so a host theme can override a bundled theme without
editing package files.

Each theme contains theme.json and views/. Theme settings and homepage sections
are stored in the host database and survive package upgrades.
