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
   teamName = teamArray[fields];
   sql="update go_teams_lu set teamName='" team "',stadiumName='" stadium "', stadiumAddress='" address "', stadiumCity='" city "',stadiumState='" state "' where teamName='" teamName "'";
   print sql delimiter;
}

