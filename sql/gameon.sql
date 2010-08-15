/*
  Author: Julio Hernandez-Miyares
  Date: April 4,2010 
  Updated: Saturday May 1,2010

  Normalized tables will not be used for direct query from the applications
  either denormalized mysql tables or from lucene index or from a denormalized
  cache layer.
  These tables will be used as master persistence managed by CMS and automated ingestion tools/processes
*/
/* Conventions - every table will have two fields , createdDate and modifiedDate of type timestamp that will persist the time when the record was first created and the last time it was modified. Just a simple audit trail. Further tables/logs will be used to keep more detailed transactional information about user and system activity.
Table names with the suffix _lu represent Look Up tables
Table Names with the suffix _dn represent denormalized tables 
*/
DELIMITER //

use jittrgameon
//
drop table if exists go_user
//
/* master table for users - very little extra information about user outside of what they volunteer when they 
 * authenticate with one of the large social networks.
 * The purpose of the record for each user in go_user is to tie all of the other user records together without having to
 * have an unwieldy massively denormalized table 
 * Will have to build application logic in case someone wipes away their simple identifer and re-ups , 
 * should all of the social networks supported credentials be viewed as unqiue ? I believe so making it easier to fine someone
 * who has had the master record deleted.
 * Does it make sense to have a master record with no defined social network crendentials? Not much sense or utility especially if gameon does not
 * define its own social network functionality. 
 * privacy is initially confined to private or public managed through boolean - true = private, false = public
 *
 * primaryNetwork field  indicates the primary network used as the identity of the user, Initially faceboom,twitter or foursquare. Wil consider aim 
 * based on whether they support OAuth authentication (95% certain they do)
 */
create table go_user (
      userID integer primary key auto_increment not null,
      userName varchar(50) not null default 'Julio',
      password varchar(32) not null,
      firstName varchar(50) null,
      lastName varchar(50) null,
      name varchar(100) null,
      privacy boolean not null default true,
      bankBalance float not null default 0,
      primaryNetworkID int  null default 0,
      primaryNetworkName varchar(25) null,
      createdDate timestamp not null default current_timestamp(),
      modifiedDate timestamp null
)
ENGINE=INNODB
//
insert into go_user (userName,privacy, bankBalance) values ('jittrdev',false, 10000)
//
insert into go_user (userName,privacy, bankBalance) values ('jittrjulio',false, 10000)
//
insert into go_user (userName,privacy, bankBalance) values ('jittrjohn',false, 10000)
//
insert into go_user (userName,privacy, bankBalance) values ('jittrralph',false, 10000)
//
/* social network attributes for a particular user
 * no new social network envisioned as part of GameOn but leveraging existing 
 * large engagement sites. Foursquare include because of it's evolving role in
 * location services
 * TODO - should we include AOL Settings - AIM ID? 
* TODO - included icq because of it's penetration outside the USA and it's impending spinoff from AOL
* fields with the ID suffix store the userID for the particular social network
* fields with the Default suffix are booleans of whether a "bet" is syndicated by default to that user's particular social network.
d* fields with OAuth within their body represent Open Auth credentials necessary for the app to communicate with the social network on the user's behalf. No such fields exist for AIM and ICQ at the moment as I am not aware if they are integrated with OAuth
 */
drop table if exists go_userSettings
//
create table go_userSettings
(
    userID integer primary key not null,
    foursquareID varchar(50) null,
    twitterID varchar(50) null,
    facebookID varchar(50) null,
    aimID varchar(50) null,
    icqID varchar(50)  null,
    twitterDefault boolean not null default false,
    facebookDefault boolean not null default false,
    foursquareDefault boolean  not null default false,
    facebookOAuthToken varchar(255)  null,
    facebookOAuthTokenSecret varchar(255) null,
    facebookImageUrl varchar(255),
    twitterOAuthToken varchar(255) null,
    twitterOAuthTokenSecret varchar(255) null,
    twitterImageUrl varchar(255),
    foursquareOAuthToken varchar(255) null,
    foursquareOAuthTokenSecret varchar(255) null,
    foursquareImageUrl varchar(255),
    lastSync datetime null,
    createdDate timestamp not null default current_timestamp(),
    modifiedDate timestamp null 
)
ENGINE=INNODB
//
insert into go_userSettings (userID) values (1);
//
/*
go_userBank - 1:1 mapping between user record in go_users. Maintains top level balance information 
for the user. 
    currentInPlayWagers - total of current wagers that have not settled
    bankBalance - funds in account
    overDraftLine - total amount that can be borrowed to cover wagers and perhaps pay
    overDraftInUse - amount of overdraft tapped
*/
drop table if exists go_userBank
//
create table go_userBank (
   userID int not null,
   currentInPlayWagers int not null default 0,
   bankBalance int not null default 0,
   overDraftLine int not null default 0, 
   overDraftLineUsed int not null default 0,
   createdDate timestamp not null default current_timestamp(),
   modifiedDate timestamp null,
   PRIMARY KEY(userID)  
)
ENGINE INNODB
//	
/* Transactional detail of user Bank.
   contains a record of every wager whether current, reconciled (ended with win or lost), outstanding 
   because of dispute or cancelled
   wagerType
         initiater
         took the bet
   wagerStatus
         open - bet has not been reconciled yet
         closed - event underlying the bet has occurred, winner determined and funds reconciled
         cancelled - bet has been cancelled
         disputed - results of event are disputed    
*/
drop table if exists go_userBankDetail
//
create table go_userBankDetail (
   userID int not null,
   gameID int not null,
   transactionID int not null AUTO_INCREMENT,
   wagerTypeID int not null default 0,
   wagerType varchar(25) null,
   wagerStatusID int not null default 0,
   wagerStatus varchar(25) null,
   createdDate timestamp not null default current_timestamp(),
   modifiedDate timestamp null,
   PRIMARY KEY(transactionID)  
)
ENGINE INNODB
//

/* listings of all games public and user defined */
/* if the game is a public game, will have a reference to the Master in go_publicgames*/
/* A public game is an artifact to represent contests in the college/professional ranks
   that are part of the the well known public sports arena. Super Bowl, NCAA MArch Madness, Division 1 NCAA football,
   The Mets Versus Phillies etc etc
  The assumption is a feed or scape of  sites that maintain that information will serve as a lookup for those that are interested in wagering
   in these types of events. A user defined game which is also stored in the same table, can make reference to a public game, for example, Alex Rodriguez will strike out 3 times in the game against the Boston Redsox on April 10th. Nevertheless it is different then the typical winner/loser/covering spread type of bet. Similarly user defined games are countably infinite and can be as personal as to a small network of people as the imagination can fathom.

  id - unique id for the wager
  publicGameID - reference to a table defining the "public game"
  createdBy - unique USERID of person setting up wager, refers to Id in go_users
  title, eventName, description are meta-data fields mostly for UI purposes to provide context/info about the event itself 
  subscriptionClose - timestamp of when the wagering window closes. After this time, no more parties can subscribe to the wager.

 added pivotDate and pivotCondition for date driven wagers - pivotCondition , before, on, after , between
 pivotDate is the date the pivotCondition will operate against.

  August 14 - changed sport to sportID
              added expirationDateiTime datetime , lifetime of  bet. If no resolution by this date/time, the bet is liquidated with no winner and loser 
              added closeDateTime datetime , the point no more bets are accepted for the Game (changed from subscriptionClose datetime
              the DB default are very restrictive for both of these fields. Expectation is the application will always transmit a value for these fields
              changed wagerUnits from int to decimal(7,2)
              subscriptionLimit - maximum number of people that are allowed to take the bet. Associated with limit of liability for the initiator. This is only applicable for a 1 -> Many bet 
              numberOfSubscribers - number of acceptors of the bet/game. Adding this field obviates the need to calculate a more intensive select on go_gameSubscribers to get the adoption of the bet
              *NOTE - mysql does not allow current_timestamp as default for a datetime field
*/
drop table if exists go_games
//
create table go_games (
   gameID varchar(25) not null,
   publicGameID int null default 0,
   createdByUserID  int  not null,
   createdByUserName varchar(50) not null,
   title varchar(50) not null,
   eventName varchar(50) null,
   wagerTypeID int  null default 0,
   wagerType varchar(255) null,
   wagerUnits decimal(7,2)  not null default 1,
   date datetime null,
   description varchar(255) null,
   type  int not null default 0,
   typeName  varchar(50) null ,
   pivotDate datetime null,
   pivotCondition varchar(10) null,
   sportID int not null default 0,
   sportName varchar(50) null,
   closeDateTime datetime not null default '0000-00-00 00:00:00', 
   expirationDateTime datetime not null default '0000-00-00 00:00:00' ,
   syndicationUrl varchar(255) not null,
   subscriptionLimit int not null default 0,
   subscriptionOpen boolean not null default false,
   numberOfSubscribers int not null default 0,
   createdDate timestamp not null default current_timestamp(),
   modifiedDate timestamp null,
   PRIMARY KEY(gameID)
)
ENGINE INNODB
//
/* Will contain a record for each combination of a "game" - a record defined in go_games, and a person who has
   taken the wager (subscribed). This will most likely be the largest table in terms of records in GameOn schema
   Only one record per gameID can have the creator field set to true per initial requirements
   TODO:  - position field meant to indicate what side of the bet. More thought required on this depending on the types of
   bets
*/
drop table if exists go_gamesSubscribers
//
create table go_gamesSubscribers (
   gameID varchar(25) not null,
   userID int not null,
   creator boolean not null default false,
   position int not null default 0, 
   wagerUnits  int  not null default 0,
   createdDate timestamp not null default current_timestamp(),
   modifiedDate timestamp null,
   PRIMARY KEY(gameID,userID)  
)
ENGINE INNODB
//

/* listing of all public games - college and professional */
drop table if exists go_publicgames
//
create table go_publicgames (
   gameID int not null AUTO_INCREMENT, 
   title varchar(50) not null,
   eventName varchar(50) null,
   date timestamp null,
   description varchar(255) null,
   type  int not null default 0,
   sportID int not null default 0,
   sportName varchar(25) not null,
   leagueID int not null default 0,
   leagueName varchar(25) not null,
   season int null,
   seasonWeek int null,
   createdDate timestamp not null default current_timestamp(),
   modifiedDate timestamp null,
   PRIMARY KEY (gameID),
   UNIQUE INDEX(title)
)
ENGINE=INNODB
//
/* Denormalized version of go_publicgames
   This is the table to be used to data power the webservice used from the Appication
   to return public games 
*/
drop table if exists go_publicgames_dn
//
create table go_publicgames_dn (
   gameID int not null,
   title varchar(50) not null,
   date timestamp null,
   typeName varchar(50) not null,
   teamName1 varchar(50) null,
   teamName2 varchar(50) null,
   sportName varchar(50) not null,
   leagueName varchar(50) not null,
   createdDate timestamp not null default current_timestamp(),
   modifiedDate timestamp null,
   PRIMARY KEY (gameID)
)
ENGINE=INNODB
//
/* Table which defines the "teams" or "individuals" that are part of an event such as type "team" or "tournament"
   id is used as a key to id field in go_publicgames table
   teamID is a key to the id field in go_teams_lu  table
*/
drop table if exists go_publicgames_combatants
//
create table go_publicgames_combatants (
   gameID int not null,
   teamID int not null,
   homeTeam int not null default 0,
   createdDate timestamp not null default current_timestamp(),
   modifiedDate timestamp null,
   PRIMARY KEY (gameID,teamID)
)
ENGINE=INNODB
//

/* Lookup tables */
/* Currency types for Wagers - the wager types is free form allowing users to wager using any
   device. Nevertheless there are a few system defined types that will have system defined rules associated with them
*/
drop table if exists go_wagerTypes_lu
//
create table go_wagerTypes_lu (
  id int not null AUTO_INCREMENT,
  wagerType varchar(255) not null,
  createdDate timestamp not null default current_timestamp(),
  modifiedDate timestamp null,
  PRIMARY KEY(id),
  UNIQUE INDEX(wagerType)
)
ENGINE=INNODB
//
insert into go_wagerTypes_lu (wagerType) values ("User Defined")
//
insert into go_wagerTypes_lu (wagerType) values ("Virtual Currency")
//
insert into go_wagerTypes_lu (wagerType) values ("$ Dollars")
//
/* Professional and Colleigette teams */
drop table if exists go_teams_lu
//
create table go_teams_lu (
  id int not null AUTO_INCREMENT,
  teamName varchar(50) not null,
  teamNameNormalized varchar(50) not null,
  teamLogoURL varchar(255) null,
  sportID int  not null default 0,
  sportName varchar(50) not null,
  stadiumName varchar(100) null,
  stadiumAddress varchar(100) null,
  stadiumAddress2 varchar(100) null,
  stadiumCity varchar(75) null,
  stadiumState varchar(50) null,
  stadiumLatitude int not null default 0,
  stadiumLongitude int not null default 0,
  foursquareVenueID int null,
  createdDate timestamp not null default current_timestamp(),
  modifiedDate timestamp null,
  PRIMARY KEY(id),
  unique index indx_teamnamenormalized (teamNameNormalized),
  UNIQUE INDEX indx_teamname (teamName,sportID)
)
ENGINE=INNODB
//

drop table if exists go_sports_lu
//

/* sports - ie football, baseball , etc */
create table go_sports_lu (
  id int not null AUTO_INCREMENT,
  leagueName varchar(50) not null,
  sportName varchar(50) not null,
  createdDate timestamp not null default current_timestamp(),
  modifiedDate timestamp null,
  PRIMARY KEY(id),
  unique index(leagueName)
)
ENGINE=INNODB
//
drop table if exists go_leagues_lu
//
create table go_leagues_lu (
  id int not null AUTO_INCREMENT,
  leagueName varchar(50) not null,
  createdDate timestamp not null default current_timestamp(),
  modifiedDate timestamp null,
  PRIMARY KEY(id),
  unique index(leagueName)
)
ENGINE=INNODB
//
insert into go_leagues_lu (leagueName) values ("NFL")
//
insert into go_leagues_lu (leagueName) values ("MLB")
//
insert into go_leagues_lu (leagueName) values ("NBA")
//
insert into go_leagues_lu (leagueName) values ("NHL")
//

insert into go_sports_lu (id, leagueName, sportName) values (0,"undefined","undefined")
//
insert into go_sports_lu (leagueName,sportName) values ("NFL","Football")
//
insert into go_sports_lu (leagueName,sportName) values ("MLB","Baseball")
//
insert into go_sports_lu (leagueName,sportName) values ("NBA","Basketball")
//
insert into go_sports_lu (leagueName,sportName) values ("NHL","Hockey")
//

insert into go_teams_lu (sportID, teamName) values (3,"New York Mets")
//
insert into go_teams_lu (sportID, teamName) values (3,"New York Yankees")
//
insert into go_teams_lu (sportID, teamName) values (2,"New York Giants")
//
insert into go_teams_lu (sportID, teamName) values (2,"New York Jets")
//
insert into go_teams_lu (sportID, teamName) values (4,"New York Knicks")
//
insert into go_teams_lu (sportID, teamName) values (4,"New Jersey Nets")
//
insert into go_teams_lu (sportID, teamName) values (5,"New Jersey Devils")
//
insert into go_teams_lu (sportID, teamName) values (5,"New York Rangers")
//

/* types of "games" ie team, tournamont, etc */
drop table if exists go_types_lu
//
create table go_types_lu (
  id int not null AUTO_INCREMENT,
  typeName varchar(50) not null,
  createdDate timestamp not null default current_timestamp(),
  modifiedDate timestamp null,
  PRIMARY KEY(id),
  UNIQUE INDEX(typeName)
)
ENGINE=INNODB
//
insert into go_types_lu (id,typeName) values (0,"Undefined")
//
insert into go_types_lu (typeName) values ("Team")
//
insert into go_types_lu (typeName) values ("Tournament")
//
insert into go_types_lu (typeName) values ("Date")
//

/* rules for the particular type of game - key for driving UI flow especially configuration and determination of a wager
*/
drop table if exists go_typeRules
//
create table go_typeRules (
   id int not null AUTO_INCREMENT,
   typeID int not null,
   modifiedDate timestamp null,
   createdDate timestamp not null default current_timestamp(),
   PRIMARY KEY(id),
   UNIQUE INDEX(typeName)
)
 ENGINE=INNODB
//

insert into go_publicgames (title,date,type,sport) 
values ("New York Yankees vs New York Mets 1","2010-04-06",2,3)
//
insert into go_publicgames (title,date,type,sport) 
values ("New York Yankees vs New York Mets 2","2010-04-07",2,3)
//
insert into go_publicgames (title,date,type,sport) 
values ("New York Yankees vs New York Mets 3","2010-04-08",2,3)
//
insert into go_publicgames_combatants (gameID,teamID) values (1,2)
//
insert into go_publicgames_combatants (gameID,teamID) values (1,1)
//
insert into go_publicgames_combatants (gameID,teamID) values (2,2)
//
insert into go_publicgames_combatants (gameID,teamID) values (2,1)
//
insert into go_publicgames_combatants (gameID,teamID) values (3,2)
//
insert into go_publicgames_combatants (gameID,teamID) values (3,1)
//
DELIMITER ;
