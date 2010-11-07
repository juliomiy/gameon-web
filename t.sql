create table go_user_search (
   userID integer not null,
   body text,
   PRIMARY KEY(userID),
   FULLTEXT(body)
)
ENGINE=MYISAM

