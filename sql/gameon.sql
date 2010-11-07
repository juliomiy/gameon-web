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
      phoneNumber varchar(20) null,
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
/* go_user_search
   date: October 19,2010
   ISAM searchable table of betsquare users
*/
drop table if exists go_user_search
//
create table go_user_search (
   userID integer not null,
   body text,
   PRIMARY KEY(userID),
   FULLTEXT(body)
)
ENGINE=MYISAM
//
/* go_userFriends
   table of betsquared friends
   created by Julio Hernandez-Miyares
   date: September 2,2010
   very simple initially
   initially primarily for devleopment, your list of friends will constitute the
   users that a bet is syndicated to
*/
drop table if exists go_userFriends
//
create table go_userFriends (
      userID integer not null,
      friendUserID integer not null,
      friendUserName varchar(50) not null ,
      friendName varchar(50) null ,
      createdDate timestamp not null default current_timestamp(),
      modifiedDate timestamp null,
      PRIMARY KEY (userID, friendUserID)
)
ENGINE=INNODB
//
/* go_friendInvites
   table to manage invites of friends
   Invitees can be users already on BetSquared and those will have BS UserID or those outside of BS where the BSUserID will default
   to -0-
    inviteStatus 
            Created
            Mailed
                Accepted
                Declined
                Lapsed
    inviteNetwork - constant representing from which network the user was invited ie foursquare, facebook, twitter, BS 
    invitetorUserID, inviteeUserName , inviteNetwork together guarantte uniqueness
    use field inviteorUserID to get all the invites an individual user has outstanding
    use field inviteeBSUserID to get all the invites user has  received
    inviteStatusID values
              1 = created
              2 = invite communicated
              3-5 (for future)
              6 = declined
              7 = approved
*/
drop table if exist go_friendInvites
//
create table go_friendInvites (
      invitetorUserID integer not null,
      inviteeBSUserID integer not null default 0,
      inviteeUserName varchar(100) not null, 
      inviteStatusID integer not null default 1,
      inviteStatus varchar(25) not null default 'Created',
      inviteNetworkID int not null default 0,
      inviteNetworkName varchar(50) null,
      createdDate timestamp not null default current_timestamp(),
      modifiedDate timestamp null,
      PRIMARY KEY (invitetoruserID, inviteeUserName, inviteNetworkID),
      INDEX (inviteeBSUserID)
)
ENGINE=INNODB
//

/* go_userDashboard
   normalized table that aggregates user metrics using the application
   keyed by the various manors of ascertaining a user's identity
*/
drop table if exists go_userDashboard
//
create table go_userDashboard (
      userID integer primary key not null,
      foursquareID varchar(50) null,
      twitterID varchar(50) null,
      facebookID varchar(50) null,
      totalBets integer not null default 0,
      totalBetsInitiated integer not null default 0,
      totalBetsAccepted integer not null default 0,
      totalWins integer not null default 0,
      totalLoses integer not null default 0,
      createdDate timestamp not null default current_timestamp(),
      modifiedDate timestamp null
)
ENGINE=INNODB
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
/* go_ActivityRewards_lu
   equates Activity with a reward for imbibing in the activity
   The considerationID is a lookup to the consideration Units 
   for convenience, will also store the name of the consideration

   October 25,2010 - for initial req, believe I can get away from having two tables to manage the
   activity rewards.
rewardType can be percentage of something or absolute points 
*/
drop table if exists go_activityRewards
//
create table go_activityRewards (
   activityID integer not null AUTO_INCREMENT,
   activityName varchar(50) not null,
   rewardType varchar(25) not null default 'absolute',
   considerationID integer not null default 0,
   considerationName varchar(50) not null,
   amount    decimal(7,2) not null default 0,
   percentage decimal(2,2) not null default 0,
   createdDate timestamp not null default current_timestamp(),
   modifiedDate timestamp null,
   PRIMARY KEY(activityID),  
   UNIQUE INDEX(activityName)
)
ENGINE INNODB
//	
insert into go_activityRewards (activityName,considerationName,amount) values ('checkin','',100)
//
insert into go_activityRewards (activityName,considerationName,amount) values ('register','',5000)
//
/* go_ConsiderationConversion
*/
drop table if exists go_considerationConversion
//
create table go_considerationConversion (
   fromConsiderationID integer not null,
   toConsiderationID integer not null,
   fromConsiderationName varchar(50) not null,
   toCOnsiderationName varchar(50) not null,
   ratio decimal(4,3) not null default 0,
   createdDate timestamp not null default current_timestamp(),
   modifiedDate timestamp null,
   PRIMARY KEY(fromConsiderationID,toConsiderationID)  
)
ENGINE INNODB
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
      modified: by Julio Hernandez-Miyares on October 25,2010
      to handle transactions to manage ducketts balance
*/
drop table if exists go_userBankDetail
//
create table go_userBankDetail (
   userID int not null,
   gameID int not null default 0,
   bs_transactionID int not null AUTO_INCREMENT,
   transactionID varchar(50) null  ,
   transactionTypeID int not null default 1 ,
   transactionTypeName varchar(25) not null default 'wager',
   transactionAmount decimal(8,2) not null default 0,
   wagerTypeID int not null default 0,
   wagerType varchar(25) null,
   wagerStatusID int not null default 0,
   wagerStatus varchar(25) null,
   createdDate timestamp not null default current_timestamp(),
   modifiedDate timestamp null,
   PRIMARY KEY(bs_transactionID)  
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
   leagueID int null default 0,
   leagueName varchar(50) null,
   numberSubscribed int not null default 0,
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
/* Contains a record for each Bet Subscriber to a 
   team event which is characterized as 2 teams playing for win/lose. 
   The expectation is the game is defined in go_publicgames
   Initially, position will be 1 for win , 0 for lose though 
   win will be the normal state as the user will select one of two teams to win
   initiatorFlag = 1 for the initiator of the bet, 0 for someone that takes the bet
*/
drop table if exists go_gameSubscribers_Team
//
create table go_gameSubscribers_Team (
   gameID varchar(25) not null,
   publicGameID int null default 0,
   userID int not null,
   initiatorFlag int not null default 0,
   position int not null default 1,
   teamID int not null,
   teamName varchar(50) not null,
   modifiedDate timestamp null,
   createdDate timestamp not null default current_timestamp(),
   PRIMARY KEY(gameID,userID)
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

/* go_gameInvite
   table will manage the invitations for a bet
    inviteStatus - 0 for open to accepted bets, 1 = accepted , 2 = declined
    privateFlag - 1 , only those that specific invitees can accept, 0 means open to all
            current default for simplicity is OPEN (0)
             2 is open with explicit invites to individuals
    inviteKey - special alpha-numeric key that describes the nature of the invite
    numberAccepted - numberrof people accepting invite. If it is a peer to peer the max is 1
    numberDeclined- number of people declining invite. If it is a peer to peer the max is 1
    gameID - key into go_games table which contains the definition of the bet
*/
drop table if exists  go_gameInvite
//
create table go_gameInvite (
   gameID varchar(25) not null,
   inviteKey varchar(255) not null,
   privateFlag int not null default 0,
   inviteStatus integer not null default 0,
   numberAccepted integer not null default 0,
   numberDeclined integer not null default 0,
   createdDate timestamp not null default current_timestamp(),
   modifiedDate timestamp null,
   PRIMARY KEY( inviteKey )  
)
ENGINE INNODB
//
/* go_gameInviteDetail
   created by Julio Hernandez-Miyares
   date: September 2,2010
   A record for each explicit invite for a bet
   Added inviteeUserName - denormalization
   added go_inviteStatusName (denorm and visually understand the state), changed to inviteStatusID as it is an id and
   reset default to 1 (created) which is inline wirg friend invite constants/states
   added closeDatetime which will match what is in go_games. This is the date/time one can accept the bet till.
*/
drop table if exists go_gameInviteDetail
//
create table go_gameInviteDetail (
   gameID varchar(25) not null,
   inviteKey varchar(255) not null,
   createdByUserID integer not null,
   createdByUserName varchar(50) not null,
   inviteeUserID integer not null,
   inviteeUserName varchar(50)  null,
   inviteStatusID integer not null default 1,
   inviteStatusName varchar(25) not null default 'created',
   closeDateTime  datetime not null default '0000-00-00 00:00:00' , 
   createdDate timestamp not null default current_timestamp(),
   modifiedDate timestamp null,
   PRIMARY KEY( gameID, createdByUserID,inviteeUserID )  
)
ENGINE INNODB
//

/* go_gameInvitePrivate is associated with the go_gameInvite Table
   it is used for when there are explicit invites to a bet/game
   instead of just a mass fully open invite for anyone that 
   accepts the gameInvite Link

   inviteType 1=email, 2=facebook, 3 = twitter, 4 = sms ,5 = BetSquared User,  0 = unknown would generally mean an error state
   for email, a cron job will look for records where inviteSendDate is null and inviteType = 1

   inviteAddress will use the address to send the invite to,
   inviteAccepted 0 = no, 1 = yes, 2 = declined 
   This is a normalized table which is 1 to many with go_games depending on explicit invites so it can get large quickly
   but this design should suffice for the beginning
*/
drop table if exists go_gameInvitePrivate
//
create table go_gameInvitePrivate(
   gameID varchar(25) not null,
   inviteKey varchar(255) not null,
   inviteAddress varchar(255) not null,
   inviteType integer not null default 0,
   inviteAccepted integer not null default 0,
   inviteSendDate datetime null,
   createdDate timestamp not null default current_timestamp(),
   modifiedDate timestamp null,
   PRIMARY KEY( gameID, inviteKey, inviteAddress  )  
)
ENGINE INNODB
//

/* listing of all public games - college and professional */
* for convenience, since a large majority of public games will be 2 team sports, will denormalize team1 and team2 into
this table folding in the keys to go_publicgames_combatants
*/
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
   team1ID integer not null default 0,
   teamName1 varchar(50) ,
   team2ID integer not null default 0,
   teamName2 varchar(50) ,
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
  leagueName varchar(50) not null,
  leagueID int  not null default 0,
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
/* table to hold the bonus rules for activities
   author: Julio Hernandez-Miyares
   date: October 25,2010
*/
drop table if exists go_bonusRules_lu
//
create table go_bonusRules_lu (
   bonusName varchar(25) not null,
   activityReward
   rewardType
   modifiedDate timestamp null,
   createdDate timestamp not null default current_timestamp(),
   PRIMARY KEY(bonusName)
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
