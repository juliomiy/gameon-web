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
   if (fields >= 2) 
     searchterm= teamArray[1] " " teamArray[2];
   else
     searchterm=teamArray[1];

   sql="update go_teams_lu  set teamName ='" team "', nickName='" nickname "',stadiumName='" stadium "',stadiumAddress='" address "',stadiumCity='" city "',stadiumState='" state "' where sportID=6 and teamName like '%"searchterm"%'";
   print sql delimiter;
}

