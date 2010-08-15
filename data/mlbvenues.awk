BEGIN {
   print "delimiter ;";
   FS="|";
   delimiter=";";
}
{
   stadium=$1;
   address=$2;
   city=$3;
   state=$4;
   team=$5;

   fields=split(team,teamArray," ");
   nickname= teamArray[fields];
   sql="insert into go_teams_lu (teamName,nickName,stadiumName,stadiumAddress,stadiumCity,StadiumState,sportID) values ('" team "','" nickname "','"  stadium "','"  address "','"  city "','" state "',3)";
   print sql delimiter;
}

