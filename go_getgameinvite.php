?php>
/* generate list of games you are invited to 
select * from go_gameInviteDetail d ,go_games g where d.inviteeUserID=1 and d.inviteStatus=0 and d.gameID = g.gameID;

want to include subscriptionOpen (date/time) on go_gameInviteDetail to filter out those invites that are no longer open for taking

Need to update subscriptionOpen field on go_game - right now defaulting to zero. 


use constant values for inviteStatus which should better be called inviteStatusID
may want tp synchronize using similar values to the inviteFriends variant of the same field
*/

?>


