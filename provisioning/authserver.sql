REPLACE INTO `Client` (`id`, `random_id`, `redirect_uris`, `secret`, `allowed_grant_types`, `name`, `preApproved`, `preApprovedScopes`, `groupRestriction_id`, `maxScopes`)
    VALUE (8, '6db2htrz9u4o84w4oskk8so84cgog0wok00ockokgws40cw8o0', 'a:1:{i:0;s:31:"http://192.168.80.7/login/oauth";}', '17oj5x0amcao408440wck8gckoo48okws48sogw0wsks4sg8cs', 'a:2:{i:0;s:18:"authorization_code";i:1;s:13:"refresh_token";}', 'Enrollments dev', 0, NULL, NULL, 'profile:username,profile:realname,profile:groups,property:read,property:write');

REPLACE INTO `ApiKey` (`id`, `scopes`, `secret`, `name`)
    VALUE (5, 'r_group,r_profile_email', '61fnbx6z9r8kkwos80c4o8kc0s04gckwcgc8wsc4s48ososows', 'Enrollments dev');

REPLACE INTO `PropertyNamespace` (`name`, `public_readable`, `public_writeable`)
    VALUE ('preferences', 1, 1);

REPLACE INTO `auth_group` (`id`, `name`, `exportable`, `no_users`, `no_groups`, `display_name`, `user_joinable`, `user_leaveable`)
    VALUES
      (1, '%sysops', 1, 0, 1, 'Sysops', 0, 1),
      (2, 'enrollments_admin', 1, 1, 0, 'Enrollments admin', 0, 0),
      (3, 'enrollments_backend', 1, 0, 0, 'Enrollments backend access', 0, 0);

REPLACE INTO `group_group` (`group_source`, `group_target`)
    VALUES
      (2, 1), -- enrollments_admin <- %sysops
      (3, 2); -- enrollments_backend <- enrollments_admin

REPLACE INTO `group_user` (`group_id`, `user_id`)
  SELECT 1, `id` FROM `auth_users` WHERE `username` = 'admin';
