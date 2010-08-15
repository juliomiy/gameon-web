BEGIN {
  print "DELIMITER ;"
}
{
print "insert into go_teams_lu (teamName, sportID,sportName) values('" $1 "'"  $2 ",'Football')";
print ";"
}
