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
INSERT INTO `goods_reviews` (`user_id`, `good_id`, `rate`, `comment`)
VALUES
(1,2,4,'Some good fresh potato'), 
(2,4,5, 'Excelent Gavai Kiwi'), 
(4,6,2,'Cheap China bike. Mdahhh');

