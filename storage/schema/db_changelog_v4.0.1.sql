INSERT INTO `metas` (`url`, `title`, `description`, `keywords`) VALUES ('privacy-policy', 'Privacy Policy', 'Privacy Policy', '');

INSERT INTO `permissions` (`id`, `group`, `name`, `display_name`, `description`, `user_type`, `created_at`, `updated_at`) VALUES
(198, 'Cache Clear', 'view_cache_clear', 'View Cache Clear', 'View Cache Clear', 'Admin', '2023-11-14 21:56:29', '2023-11-14 21:56:29'),
(199, 'Cache Clear', 'add_cache_clear', NULL, NULL, 'Admin', '2023-11-14 21:56:29', '2023-11-14 21:56:29'),
(200, 'Cache Clear', 'edit_cache_clear', NULL, NULL, 'Admin', '2023-11-14 21:56:29', '2023-11-14 21:56:29'),
(201, 'Cache Clear', 'delete_cache_clear', NULL, NULL, 'Admin', '2023-11-14 21:56:29', '2023-11-14 21:56:29');

INSERT INTO `permission_role` (`role_id`, `permission_id`) VALUES
(1, 198),
(1, 199),
(1, 200),
(1, 201);
