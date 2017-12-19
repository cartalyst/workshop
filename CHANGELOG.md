# Workshop Change Log

This project follows [Semantic Versioning](CONTRIBUTING.md).

## Proposals

We do not give estimated times for completion on `Accepted` Proposals.

- [Accepted](https://github.com/cartalyst/workshop/labels/Accepted)
- [Rejected](https://github.com/cartalyst/workshop/labels/Rejected)

---

### v3.0.7 - 2017-12-19

`FIXED`

- Added missing `bulk_actions` permission.

### v3.0.6 - 2017-10-26

`FIXED`

- Data grid sort syntax on the admin controller stub.

### v3.0.5 - 2017-08-10

`REVISED`

- Updated routes to match the latest style.

### v3.0.4 - 2017-05-05

`FIXED`

- Permissions generation.

### v3.0.3 - 2017-03-19

`FIXED`

- Data Grid Markup Fixes.

### v3.0.2 - 2017-03-11

`FIXED`

- A bug initializing the date picker.

### v3.0.1 - 2017-02-27

`FIXED`

- A bug refreshing the grid upon item deletion.

### v3.0.0 - 2017-02-24

Updated for Platfrom 6 & Platform 7.

### v2.0.9 - 2016-07-18

`FIXED`

- Fixed a duplicate `href` attribute on generated data grid result templates.

### v2.0.8 - 2016-05-17

`FIXED`

- Fixed event handler which had an incorrect and unnecessary argument.

### v2.0.7 - 2016-05-13

`UPDATED`

- Tweaked how events are fired on deletions.

### v2.0.6 - 2015-12-14

`FIXED`

- Loosened platform/foundation version composer.json stub.
- Use https url for the cartalyst repository on the composer.json stub.

### v2.0.5 - 2015-07-17

`FIXED`

- Typo on the `index.js.stub` file for the bulk actions event listener.

### v2.0.4 - 2015-07-12

`FIXED`

- Duplicated comment on the admin index view stub.
- Form element indentation on the form generator.

`ADDED`

- Menu regex to the generated extension.php file menus.
- Named route to the frontend routes, to keep consistency.

`UPDATED`

- index.js stub to keep consistency.

`REMOVED`

- Unused import declarations from the repository stub file.

### v2.0.3 - 2015-06-30

`FIXED`

- Data Grid header alignment.
- Blade page section on the `view-frontend-index.blade.stub` file.
- Form stub not having a unique id.
- Typo on `form-checkbox.stub` file.
- Docblock on the data handler property on the `repository.stub`.

`UPDATED`

- Consistency tweaks.

### v2.0.2 - 2015-05-04

`REVISED`

- Moved providers into a separate directory.
- Updated composer.json stub dependencies.

### v2.0.1 - 2015-02-27

`REVISED`

- Split resources into subdirs for `Handlers`, `Repositories` and `Validators`.

### v2.0.0 - 2015-02-17

`REVISED`

- Loosen dependencies for L5.
- Remove `package` call from the service provider stub.

### v1.0.6 - 2015-07-17

`FIXED`

- Typo on the `index.js.stub` file for the bulk actions event listener.

### v1.0.5 - 2015-07-12

`FIXED`

- Duplicated comment on the admin index view stub.
- Form element indentation on the form generator.

`ADDED`

- Menu regex to the generated extension.php file menus.
- Named route to the frontend routes, to keep consistency.

`UPDATED`

- index.js stub to keep consistency.

`REMOVED`

- Unused import declarations from the repository stub file.

### v1.0.4 - 2015-06-30

`FIXED`

- Data Grid header alignment.
- Blade page section on the `view-frontend-index.blade.stub` file.
- Form stub not having a unique id.
- Typo on `form-checkbox.stub` file.

`UPDATED`

- Consistency tweaks.

### v1.0.3 - 2015-02-17

`REVISED`

- Updated data grids to use a transformer and edit_uri.
- Removed duplicate `package` call on the service provider stub.

`FIXED`

- Alerts on forms.
- Regex for parent admin menus.

### v1.0.2 - 2015-02-11

`FIXED`

- Fixed the validator path on the RepositoryGenerator.

### v1.0.1 - 2015-02-11

`UPDATED`

- Updated extension.stub to include permissions and application arguments.

### v1.0.0 - 2015-01-28

`INIT`

- DataGrid Generator
- Extension Generator
- Extension Theme Generator
- Form Generator
- Migrations Generator
- Repository Generator
