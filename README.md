<div id="header" align="center">
    <img src="https://media.giphy.com/media/v1.Y2lkPTc5MGI3NjExcTl3dWs3eTE5bmpsaGx5a3ZtbGRwYXF6ZmJ4NzV5M2F1NnBobXZvZyZlcD12MV9pbnRlcm5hbF9naWZfYnlfaWQmY3Q9Zw/f3KwliaH4MLtli8z7D/giphy.gif" width="200" height="150">
    <div id="badges">
        <a href="https://www.linkedin.com/in/santanudatta94/">
            <img src="https://img.shields.io/badge/LinkedIn-blue?style=for-the-badge&logo=linkedin&logoColor=white" alt="LinkedIn Badge"/>
        </a>
        <a href="https://twitter.com/SantanuDatta94">
            <img src="https://img.shields.io/badge/Twitter-blue?style=for-the-badge&logo=twitter&logoColor=white" alt="Twitter Badge"/>
        </a>
    </div>
    <img src="https://komarev.com/ghpvc/?username=SantanuDatta&style=flat-square&color=blue" alt=""/>
</div>

## Library Management System

This is a project designed to manage and maintain the records of books in a library and can be lend to those who wants to borrow but will be fined if they delay to return a book on time. Its been created using Filament v3.

## Features

- **Author Management**: Author based books with their bio.
- **Publisher Management**: Publishers who publish author books and supply them.
- **Genre Management**: Each book have their own genre so can set them up based on that book.
- **Book Management**: Allows you to add, update, and delete books in the library.
- **User Management**: Manage user accounts including creating new users, updating user information, and deleting users.
- **Borrowing and Returning Books**: Users can borrow and return books, and the system keeps track of all transactions.
- **Permission Based Role**: Roles such as Staff can create borrowers but cant remove them unless admin decides to.

## Getting Started

If you want to use my project first you can either download the zip file or you can clone it using the command to your designated location

```bash
git clone https://github.com/SantanuDatta/LibrarySystem.git
```

Setup your environment

```bash
cd LibrarySystem
cp .env.example .env
composer install
```

Make sure to generate a new key in the `env` and make necessary changes

```bash
php artisan key:generate
```

Generate Storage Link

```bash
php artisan storage:link
```

After create project, Run migration & seeder

```bash
php artisan migrate
php artisan db:seed
```

or

```bash
php artisan migrate:fresh --seed
```

Now you can access login with `/admin` path where once you enter the default login data is given through which you can easily access the dashboard.

## Plugins

These are [Filament Plugins](https://filamentphp.com/plugins) that used for this project.

| **Plugin**                                                                                          | **Author**                                          |
| :-------------------------------------------------------------------------------------------------- | :-------------------------------------------------- |
| [Filament Spatie Media Library](https://github.com/filamentphp/spatie-laravel-media-library-plugin) | [Filament Official](https://github.com/filamentphp) |
| [Filament Spatie Settings](https://github.com/filamentphp/spatie-laravel-settings-plugin)           | [Filament Official](https://github.com/filamentphp) |

## License

Library Management System is provided under the [MIT License](LICENSE).
