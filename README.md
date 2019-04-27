# ChatAppServer
This project was made as an assessment for a job interview.

### Objective
Write a very simple ‘chat’ application backend in PHP. 
A user should be able to send a simple text message to another user and a user should be able to get the messages sent to him and the author users of those messages. 
The users and messages should be stored in a simple SQLite database. 
All communication between the client and server should happen over a simple JSON based protocol over HTTP (which may be periodically refreshed to poll for new messages).
A GUI, user registration and user login are not needed but the users should be identified by some token or ID in the HTTP messages and the database. 
You have the freedom to use any framework and libraries;

### Setup

Normally I like to deliver this kind of project with a simple setup.sh script that sets the project up in one go.
But this time I will not do that, mainly because this is running with Vue and Symfony that provide servers of their own.

Therefore this project has a very basic setup that has some requirements. If you can use the Servers of Symfony and Vue(npm) on your machine you should be able to run the project, please take a look at the documentation of [Vue-cli](https://cli.vuejs.org/) and [Symfony](https://symfony.com/doc/current/setup/built_in_web_server.html) if you are havin trouble.

#### Steps
- [ ] clone this project
- [ ] clone the [Frontend](https://github.com/fernandofreamunde/ChatApp) of it... 
- [ ] on this project folder run `composer install`
- [ ] on this project folder run `php bin/console doctrine:database:create`
- [ ] on this project folder run `php bin/console doctrine:migrations:migrate`
- [ ] on this project folder run `php bin/console server:run`
- [ ] on the Frontend folder run `npm install`
- [ ] on the Frontend folder run `npm run dev`
 
 Your browser will open on the project url automatically and you will be greeted into the app.
 
#### Usage

In order to see the application running you should register at leaset 2 users, and open them in 2 tabs **caution** if you refresh the tab you will have to login again.

Invite each other with the email address and start chatting, everything should be working nicely.

#### Notes

I really am not that tallented at making things pretty yet so the app is a bit ugly, and since I'm quite new to Fronend stuff there are probably things from bootstrap that I could use but I didnt because of my ignorance, I figured that since this is a backend assessment would not be a problem :)
