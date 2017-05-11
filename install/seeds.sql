-- Some users
INSERT INTO `users` (`id`, `login`, `password`)
VALUES
(1,'admin', '$2y$10$6/sJvUTnuZgQZjNXrYEvlOHUOoEw60PNGqMuKxiNl19STvnPh3vlK'), -- pass = admin
(2,'Ivan', '$2y$10$Sxqc2Uus/0DMiJOO.v2H1.alElFzKjZb/UQ9UnprE7vtohu8iR1iS'), -- pass = 1234
(3,'Petr', '$2y$10$1EJQ9utb1WOzN7nFm3Jd5eVk2AfrIedrYhmJtToL3ODndO5tWxv82'), -- pass = 12345
(4,'Igor', '$2y$10$irBDsPNF/4rgbgzfDWDdFOpQaxDu3wCf5KvAWtHACnGySiE8IuUr6'); -- pass = pp23sa


-- Some goods
INSERT INTO `goods` (`id`,`name`)
VALUES (1, 'Potato'), (2, 'Apple'), (3,'Orange'), (4,'Kiwi'), (5,'Boat'), (6,'Bike');


-- Some reviews
INSERT INTO `good_reviews` (`user_id`, `good_id`, `rate`, `comment`)
VALUES
(1,1,4,'Some good fresh potato'), 
(2,1,5,'Excelent fresh potato. Fast delivery.'), 
(4,1,3,'Some good peaces, but other not good. Delivery fast at time.'), 
(1,4,5,'Very and very nice kiwi. All loves kiwi. Awesome fast delivry like it. Recomend.'),
(2,4,4,'Yes, kiwi really delicious. Yep delivry vey fast. Recomend.'),
(2,4,5,'Excelent Gavai Kiwi. How they such fast moving? Great.'), 
(4,6,2,'Cheap China bike. Mdahhh'),
(1,3,1,'Where they storing oranges? look not good. No one start gues... Bad.');

