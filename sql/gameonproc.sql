DELIMITER // 

drop procedure if exists denormalize_go_publicgames
//

CREATE PROCEDURE denormalize_go_publicgames()
BEGIN





   /*-- Declare local variables*/
   DECLARE done BOOLEAN DEFAULT 0;
   DECLARE hometeam varchar(50);
   DECLARE team varchar(50);
   DECLARE visitingteam varchar(50);
   DECLARE gameID int;
   DECLARE previousGameID int;
  
   /*-- Declare the cursor */
   DECLARE teams CURSOR
   FOR
   SELECT c.gameID, t.teamName  FROM go_publicgames_combatants c,go_teams_lu t where c.teamID = t.id  order by c.gameID,c.homeTeam desc;

   /*-- Declare continue handler */
   DECLARE CONTINUE HANDLER FOR SQLSTATE '02000' SET done=1;

   /* populate the go_publicgames_dn table (denormalized instance of go_publicgames*/
   delete from go_publicgames_dn;

   insert into go_publicgames_dn (id,title,date,typeName,sportName,leagueName) select pg.id, pg.title, pg.date, t.typeName, s.sportName, s.leagueName 
   from go_publicgames pg,
        go_types_lu t,
	go_sports_lu s
   where pg.type = t.id and
         pg.sport = s.id;

  /* -- Open the cursor */
   OPEN teams;

   select hometeam=null;
   select visitingteam=null;
   select previousGameID=null;
   select gameID =0;
   /*-- Loop through all rows*/
   REPEAT

          update go_publicgames_dn pg set pg.teamName1=hometeam, pg.teamName2 = visitingteam; /*where pg.id = 1;*/
      /*-- Get order number */
      FETCH teams INTO gameID, team;
      IF  (previousGameID != gameID) THEN 
          select hometeam = team;
          select previousGameID = gameID;
          update go_publicgames_dn pg set pg.teamName1=hometeam, pg.teamName2 = visitingteam; /*where pg.id = 1;*/
      ELSE 
          select visitingteam="teamtest";
          update go_publicgames_dn pg set pg.teamName1=hometeam, pg.teamName2 = visitingteam; /* where pg.id = 1;*/
          select previousGameID=null,hometeam=null,visitingteam=null;
      END IF;   
   /*-- End of loop*/
   UNTIL done END REPEAT;

   /*-- Close the curso */
   CLOSE teams;

END
//
DELIMITER ;
