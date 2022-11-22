## [1.2.2](https://github.com/ForestAdmin/laravel-forestadmin/compare/v1.2.1...v1.2.2) (2022-11-22)


### Bug Fixes

* permission to avoid any conflict with laravel policy ([#62](https://github.com/ForestAdmin/laravel-forestadmin/issues/62)) ([03d5856](https://github.com/ForestAdmin/laravel-forestadmin/commit/03d5856f2369905fce34fe1f01d4b94de9948ed7))

## [1.2.1](https://github.com/ForestAdmin/laravel-forestadmin/compare/v1.2.0...v1.2.1) (2022-10-20)


### Bug Fixes

* render jsonapi when a model uses eager load relation ([#61](https://github.com/ForestAdmin/laravel-forestadmin/issues/61)) ([fdbe907](https://github.com/ForestAdmin/laravel-forestadmin/commit/fdbe907b8bea94563bd34f4a39153a14bb59eee7))

# [1.2.0](https://github.com/ForestAdmin/laravel-forestadmin/compare/v1.1.3...v1.2.0) (2022-09-14)


### Features

* **auth:** remove callbackUrl parameter on authentication ([#59](https://github.com/ForestAdmin/laravel-forestadmin/issues/59)) ([e097ff9](https://github.com/ForestAdmin/laravel-forestadmin/commit/e097ff9132c985122f7742b3e974c4f7aa4940fd))

## [1.1.3](https://github.com/ForestAdmin/laravel-forestadmin/compare/v1.1.2...v1.1.3) (2022-09-06)


### Bug Fixes

* **charts:** user with permissions level that allows charts creation or edition should always be allow to perform charts requests ([#60](https://github.com/ForestAdmin/laravel-forestadmin/issues/60)) ([b467b35](https://github.com/ForestAdmin/laravel-forestadmin/commit/b467b35d191ffe500aad029d8144f82a16a81567))

## [1.1.2](https://github.com/ForestAdmin/laravel-forestadmin/compare/v1.1.1...v1.1.2) (2022-07-22)


### Bug Fixes

* **security:** update guzzle version (with dependency) to 7.4.5 ([#58](https://github.com/ForestAdmin/laravel-forestadmin/issues/58)) ([5128787](https://github.com/ForestAdmin/laravel-forestadmin/commit/51287875c6efdc5faf1921bedb1aaab4b76c3118))

## [1.1.1](https://github.com/ForestAdmin/laravel-forestadmin/compare/v1.1.0...v1.1.1) (2022-06-27)


### Bug Fixes

* add database type timestamp for schema generation ([#57](https://github.com/ForestAdmin/laravel-forestadmin/issues/57)) ([f645277](https://github.com/ForestAdmin/laravel-forestadmin/commit/f645277d77abab1a6a78fc5acf45167d9ece2bb6))

# [1.1.0](https://github.com/ForestAdmin/laravel-forestadmin/compare/v1.0.1...v1.1.0) (2022-06-27)


### Features

* add support for multiple models directories ([#56](https://github.com/ForestAdmin/laravel-forestadmin/issues/56)) ([5947782](https://github.com/ForestAdmin/laravel-forestadmin/commit/59477821a4ab26a3f9c26ae1e9e82a7d6dc69269))

## [1.0.1](https://github.com/ForestAdmin/laravel-forestadmin/compare/v1.0.0...v1.0.1) (2022-06-07)


### Bug Fixes

* **security:** update guzzle version to 7.4.3 ([#52](https://github.com/ForestAdmin/laravel-forestadmin/issues/52)) ([f9806e3](https://github.com/ForestAdmin/laravel-forestadmin/commit/f9806e3711f22b7e8e1362450cffcaca937bffe2))

# 1.0.0 (2022-06-07)


### Bug Fixes

* **schema:** fix method that get the driver of the database ([#43](https://github.com/ForestAdmin/laravel-forestadmin/issues/43)) ([0770647](https://github.com/ForestAdmin/laravel-forestadmin/commit/0770647dfdbaeb751f9961b84f206128cc2f3992))
* authentication on production environment ([#30](https://github.com/ForestAdmin/laravel-forestadmin/issues/30)) ([3ba7872](https://github.com/ForestAdmin/laravel-forestadmin/commit/3ba78721dbe998e1007b1935486769e86f71be0a))
* deploy on production ([#35](https://github.com/ForestAdmin/laravel-forestadmin/issues/35)) ([9db8471](https://github.com/ForestAdmin/laravel-forestadmin/commit/9db8471fb50ea301a50d3bed3098bc3c9395472c))
* readme update documentation link ([#36](https://github.com/ForestAdmin/laravel-forestadmin/issues/36)) ([3dee307](https://github.com/ForestAdmin/laravel-forestadmin/commit/3dee3070910c05843ecd856c5856b0298eff4d3d))
* **dependency:** fix the dependency that serialize api response ([#34](https://github.com/ForestAdmin/laravel-forestadmin/issues/34)) ([69f0f74](https://github.com/ForestAdmin/laravel-forestadmin/commit/69f0f7438dc2d1cb597cc8aa3a4b3a86d029d11b))
* searchExtended on model without relations ([#31](https://github.com/ForestAdmin/laravel-forestadmin/issues/31)) ([c14a7a7](https://github.com/ForestAdmin/laravel-forestadmin/commit/c14a7a7fa553002d4202a38ac18d0564e67ee5b8))
* **schema:** add support on Mariadb enum ([#27](https://github.com/ForestAdmin/laravel-forestadmin/issues/27)) ([db1d3e6](https://github.com/ForestAdmin/laravel-forestadmin/commit/db1d3e65360308c8544120c308fede02322afd01))
* **schema:** added support enum for mariaDB ([#26](https://github.com/ForestAdmin/laravel-forestadmin/issues/26)) ([9206a3d](https://github.com/ForestAdmin/laravel-forestadmin/commit/9206a3d68b871fed7656316ebb51237328a0a9ab))
* fix dependency psr/simple-cache on installation ([#24](https://github.com/ForestAdmin/laravel-forestadmin/issues/24)) ([3d55fb2](https://github.com/ForestAdmin/laravel-forestadmin/commit/3d55fb230cec5653bd670181604834cbfa1708df))
* onboarding installation on php8.1 ([#25](https://github.com/ForestAdmin/laravel-forestadmin/issues/25)) ([ef22634](https://github.com/ForestAdmin/laravel-forestadmin/commit/ef22634059240ad848087d93277fbe5baab4562c))


### Features

* **auth:** add logout route ([#49](https://github.com/ForestAdmin/laravel-forestadmin/issues/49)) ([369394a](https://github.com/ForestAdmin/laravel-forestadmin/commit/369394ac8dbb880a03058101ff2165c4f9df7224))
* **authentication:** add login & callback actions ([#6](https://github.com/ForestAdmin/laravel-forestadmin/issues/6)) ([04edd06](https://github.com/ForestAdmin/laravel-forestadmin/commit/04edd06ab6c63be5180b23f49b46923fa643f795))
* **charts:** add charts support ([#17](https://github.com/ForestAdmin/laravel-forestadmin/issues/17)) ([8d850b1](https://github.com/ForestAdmin/laravel-forestadmin/commit/8d850b1f9766bdfeea3bcca9e7d726d900e16cf3))
* **config:** add include/exclude models settings ([#46](https://github.com/ForestAdmin/laravel-forestadmin/issues/46)) ([1dcab5a](https://github.com/ForestAdmin/laravel-forestadmin/commit/1dcab5a0ff64fcb08dd933eb4d5578e64235ea4a))
* **cors:** add access controll allow private network handling  ([#40](https://github.com/ForestAdmin/laravel-forestadmin/issues/40)) ([036fb08](https://github.com/ForestAdmin/laravel-forestadmin/commit/036fb08c00913b11a77c0e521c8d4fd9f3538c92))
* **crud:** create, update & delete actions ([#13](https://github.com/ForestAdmin/laravel-forestadmin/issues/13)) ([ed9e7ce](https://github.com/ForestAdmin/laravel-forestadmin/commit/ed9e7cec29d6cf4dc409d85d6096cce88160719d))
* **crud:** list and show actions [#12](https://github.com/ForestAdmin/laravel-forestadmin/issues/12) ([4cb9a74](https://github.com/ForestAdmin/laravel-forestadmin/commit/4cb9a7438125b7385d4b16530a3015570d4a04ad))
* **deploy:** add FOREST_SEND_APIMAP_AUTOMATIC env to enable automatic sending of the apimap ([#39](https://github.com/ForestAdmin/laravel-forestadmin/issues/39)) ([781f199](https://github.com/ForestAdmin/laravel-forestadmin/commit/781f199288c7f7ef1a0409c0a201ac84d1be49c7))
* **filters:** add filters behaviour ([#15](https://github.com/ForestAdmin/laravel-forestadmin/issues/15)) ([bba17bc](https://github.com/ForestAdmin/laravel-forestadmin/commit/bba17bc2c924258a28e835eafbf9bdbe70ca5f09))
* **ip-whitelist:** add ip-whitelist support ([#38](https://github.com/ForestAdmin/laravel-forestadmin/issues/38)) ([1fd2585](https://github.com/ForestAdmin/laravel-forestadmin/commit/1fd2585ca3a3ebfbe12a5590039a7cf470022abd))
* **onboard:** allow user to onboard with laravel valet ([#45](https://github.com/ForestAdmin/laravel-forestadmin/issues/45)) ([47fa556](https://github.com/ForestAdmin/laravel-forestadmin/commit/47fa556cfaaed1c8e189168c82f40892deac2981))
* **onboarding:** add a url control on forest install command ([#44](https://github.com/ForestAdmin/laravel-forestadmin/issues/44)) ([181da4c](https://github.com/ForestAdmin/laravel-forestadmin/commit/181da4c1a7daf24a47fd80684357ba60741d565b))
* **onboarding:** add new setup command ([#19](https://github.com/ForestAdmin/laravel-forestadmin/issues/19)) ([ac9ac85](https://github.com/ForestAdmin/laravel-forestadmin/commit/ac9ac855504dd01dd9dfbb820ebb4903358389bd))
* **onboarding:** update package to laravel9 ([#21](https://github.com/ForestAdmin/laravel-forestadmin/issues/21)) ([9a4bca1](https://github.com/ForestAdmin/laravel-forestadmin/commit/9a4bca17391e41098b611bf7ee8cf088e607cbba))
* **permission:** added permission layer ([#16](https://github.com/ForestAdmin/laravel-forestadmin/issues/16)) ([9273bc7](https://github.com/ForestAdmin/laravel-forestadmin/commit/9273bc7f9d144d613480d16cb33993ff589127b8))
* **schema:** build schema json file ([#10](https://github.com/ForestAdmin/laravel-forestadmin/issues/10)) ([7e795d8](https://github.com/ForestAdmin/laravel-forestadmin/commit/7e795d8809a17bda0d34a7fce6afc2d990638ce4))
* **scopes:** added scopes support  ([#18](https://github.com/ForestAdmin/laravel-forestadmin/issues/18)) ([59edad6](https://github.com/ForestAdmin/laravel-forestadmin/commit/59edad62a00d22c79e19a15e0ceb7940a4654ea9))
* **search:** add search behaviour ([#14](https://github.com/ForestAdmin/laravel-forestadmin/issues/14)) ([920af78](https://github.com/ForestAdmin/laravel-forestadmin/commit/920af789ed1abfb641d58e660e5f377ae0d9db8c))
* **smart-actions:** added smart-actions support ([#22](https://github.com/ForestAdmin/laravel-forestadmin/issues/22)) ([bdef099](https://github.com/ForestAdmin/laravel-forestadmin/commit/bdef099263cf13206fab85a8c9e314df2d8ce1be))
* **smart-collections:** add smart-collections support ([1e9f7a7](https://github.com/ForestAdmin/laravel-forestadmin/commit/1e9f7a7ab9882baa16d379e04e2bd186e20d060d))
* **smart-field:** added smart-fields and smart-relationships support ([#28](https://github.com/ForestAdmin/laravel-forestadmin/issues/28)) ([ee09b7c](https://github.com/ForestAdmin/laravel-forestadmin/commit/ee09b7c2ad08b4dc6f7e49dc6f03e641e62b3c9a))
* **smart-segment:** add smart-segments support ([#33](https://github.com/ForestAdmin/laravel-forestadmin/issues/33)) ([66d33d3](https://github.com/ForestAdmin/laravel-forestadmin/commit/66d33d328ccdffd338f55af08933505bafa42821))
* **tests:** improve all tests with factories ([#37](https://github.com/ForestAdmin/laravel-forestadmin/issues/37)) ([427b22d](https://github.com/ForestAdmin/laravel-forestadmin/commit/427b22d567b8b289dd4be90354678425257c7fd9))
* add deactivate count and refactor smart features ([#32](https://github.com/ForestAdmin/laravel-forestadmin/issues/32)) ([49cddf4](https://github.com/ForestAdmin/laravel-forestadmin/commit/49cddf4ca7ea7a1316b9e3e29b1fbcef7242c714))
* initial beta-release ([a5e39cb](https://github.com/ForestAdmin/laravel-forestadmin/commit/a5e39cb2ccb8a5004646894507df5cd0d2e09376))


### BREAKING CHANGES

* package will be available on packagist

# [1.0.0-beta.23](https://github.com/ForestAdmin/laravel-forestadmin/compare/v1.0.0-beta.22...v1.0.0-beta.23) (2022-05-17)


### Features

* **auth:** add logout route ([#49](https://github.com/ForestAdmin/laravel-forestadmin/issues/49)) ([369394a](https://github.com/ForestAdmin/laravel-forestadmin/commit/369394ac8dbb880a03058101ff2165c4f9df7224))

# [1.0.0-beta.22](https://github.com/ForestAdmin/laravel-forestadmin/compare/v1.0.0-beta.21...v1.0.0-beta.22) (2022-05-11)


### Features

* **onboard:** allow user to onboard with laravel valet ([#45](https://github.com/ForestAdmin/laravel-forestadmin/issues/45)) ([47fa556](https://github.com/ForestAdmin/laravel-forestadmin/commit/47fa556cfaaed1c8e189168c82f40892deac2981))

# [1.0.0-beta.21](https://github.com/ForestAdmin/laravel-forestadmin/compare/v1.0.0-beta.20...v1.0.0-beta.21) (2022-05-09)


### Features

* **config:** add include/exclude models settings ([#46](https://github.com/ForestAdmin/laravel-forestadmin/issues/46)) ([1dcab5a](https://github.com/ForestAdmin/laravel-forestadmin/commit/1dcab5a0ff64fcb08dd933eb4d5578e64235ea4a))

# [1.0.0-beta.20](https://github.com/ForestAdmin/laravel-forestadmin/compare/v1.0.0-beta.19...v1.0.0-beta.20) (2022-05-04)


### Features

* **onboarding:** add a url control on forest install command ([#44](https://github.com/ForestAdmin/laravel-forestadmin/issues/44)) ([181da4c](https://github.com/ForestAdmin/laravel-forestadmin/commit/181da4c1a7daf24a47fd80684357ba60741d565b))

# [1.0.0-beta.19](https://github.com/ForestAdmin/laravel-forestadmin/compare/v1.0.0-beta.18...v1.0.0-beta.19) (2022-05-03)


### Bug Fixes

* **schema:** fix method that get the driver of the database ([#43](https://github.com/ForestAdmin/laravel-forestadmin/issues/43)) ([0770647](https://github.com/ForestAdmin/laravel-forestadmin/commit/0770647dfdbaeb751f9961b84f206128cc2f3992))

# [1.0.0-beta.18](https://github.com/ForestAdmin/laravel-forestadmin/compare/v1.0.0-beta.17...v1.0.0-beta.18) (2022-04-28)


### Features

* **cors:** add access controll allow private network handling  ([#40](https://github.com/ForestAdmin/laravel-forestadmin/issues/40)) ([036fb08](https://github.com/ForestAdmin/laravel-forestadmin/commit/036fb08c00913b11a77c0e521c8d4fd9f3538c92))

# [1.0.0-beta.17](https://github.com/ForestAdmin/laravel-forestadmin/compare/v1.0.0-beta.16...v1.0.0-beta.17) (2022-04-27)


### Features

* **deploy:** add FOREST_SEND_APIMAP_AUTOMATIC env to enable automatic sending of the apimap ([#39](https://github.com/ForestAdmin/laravel-forestadmin/issues/39)) ([781f199](https://github.com/ForestAdmin/laravel-forestadmin/commit/781f199288c7f7ef1a0409c0a201ac84d1be49c7))

# [1.0.0-beta.16](https://github.com/ForestAdmin/laravel-forestadmin/compare/v1.0.0-beta.15...v1.0.0-beta.16) (2022-04-26)


### Features

* **ip-whitelist:** add ip-whitelist support ([#38](https://github.com/ForestAdmin/laravel-forestadmin/issues/38)) ([1fd2585](https://github.com/ForestAdmin/laravel-forestadmin/commit/1fd2585ca3a3ebfbe12a5590039a7cf470022abd))

# [1.0.0-beta.15](https://github.com/ForestAdmin/laravel-forestadmin/compare/v1.0.0-beta.14...v1.0.0-beta.15) (2022-04-26)


### Features

* **tests:** improve all tests with factories ([#37](https://github.com/ForestAdmin/laravel-forestadmin/issues/37)) ([427b22d](https://github.com/ForestAdmin/laravel-forestadmin/commit/427b22d567b8b289dd4be90354678425257c7fd9))

# [1.0.0-beta.14](https://github.com/ForestAdmin/laravel-forestadmin/compare/v1.0.0-beta.13...v1.0.0-beta.14) (2022-04-20)


### Bug Fixes

* readme update documentation link ([#36](https://github.com/ForestAdmin/laravel-forestadmin/issues/36)) ([3dee307](https://github.com/ForestAdmin/laravel-forestadmin/commit/3dee3070910c05843ecd856c5856b0298eff4d3d))

# [1.0.0-beta.13](https://github.com/ForestAdmin/laravel-forestadmin/compare/v1.0.0-beta.12...v1.0.0-beta.13) (2022-04-19)


### Bug Fixes

* deploy on production ([#35](https://github.com/ForestAdmin/laravel-forestadmin/issues/35)) ([9db8471](https://github.com/ForestAdmin/laravel-forestadmin/commit/9db8471fb50ea301a50d3bed3098bc3c9395472c))

# [1.0.0-beta.12](https://github.com/ForestAdmin/laravel-forestadmin/compare/v1.0.0-beta.11...v1.0.0-beta.12) (2022-04-19)


### Bug Fixes

* **dependency:** fix the dependency that serialize api response ([#34](https://github.com/ForestAdmin/laravel-forestadmin/issues/34)) ([69f0f74](https://github.com/ForestAdmin/laravel-forestadmin/commit/69f0f7438dc2d1cb597cc8aa3a4b3a86d029d11b))

# [1.0.0-beta.11](https://github.com/ForestAdmin/laravel-forestadmin/compare/v1.0.0-beta.10...v1.0.0-beta.11) (2022-04-15)


### Features

* **smart-segment:** add smart-segments support ([#33](https://github.com/ForestAdmin/laravel-forestadmin/issues/33)) ([66d33d3](https://github.com/ForestAdmin/laravel-forestadmin/commit/66d33d328ccdffd338f55af08933505bafa42821))

# [1.0.0-beta.10](https://github.com/ForestAdmin/laravel-forestadmin/compare/v1.0.0-beta.9...v1.0.0-beta.10) (2022-04-13)


### Features

* add deactivate count and refactor smart features ([#32](https://github.com/ForestAdmin/laravel-forestadmin/issues/32)) ([49cddf4](https://github.com/ForestAdmin/laravel-forestadmin/commit/49cddf4ca7ea7a1316b9e3e29b1fbcef7242c714))

# [1.0.0-beta.9](https://github.com/ForestAdmin/laravel-forestadmin/compare/v1.0.0-beta.8...v1.0.0-beta.9) (2022-04-11)


### Bug Fixes

* searchExtended on model without relations ([#31](https://github.com/ForestAdmin/laravel-forestadmin/issues/31)) ([c14a7a7](https://github.com/ForestAdmin/laravel-forestadmin/commit/c14a7a7fa553002d4202a38ac18d0564e67ee5b8))

# [1.0.0-beta.8](https://github.com/ForestAdmin/laravel-forestadmin/compare/v1.0.0-beta.7...v1.0.0-beta.8) (2022-04-05)


### Bug Fixes

* authentication on production environment ([#30](https://github.com/ForestAdmin/laravel-forestadmin/issues/30)) ([3ba7872](https://github.com/ForestAdmin/laravel-forestadmin/commit/3ba78721dbe998e1007b1935486769e86f71be0a))

# [1.0.0-beta.7](https://github.com/ForestAdmin/laravel-forestadmin/compare/v1.0.0-beta.6...v1.0.0-beta.7) (2022-04-04)


### Features

* **smart-collections:** add smart-collections support ([1e9f7a7](https://github.com/ForestAdmin/laravel-forestadmin/commit/1e9f7a7ab9882baa16d379e04e2bd186e20d060d))

# [1.0.0-beta.6](https://github.com/ForestAdmin/laravel-forestadmin/compare/v1.0.0-beta.5...v1.0.0-beta.6) (2022-03-28)


### Features

* **smart-field:** added smart-fields and smart-relationships support ([#28](https://github.com/ForestAdmin/laravel-forestadmin/issues/28)) ([ee09b7c](https://github.com/ForestAdmin/laravel-forestadmin/commit/ee09b7c2ad08b4dc6f7e49dc6f03e641e62b3c9a))

# [1.0.0-beta.5](https://github.com/ForestAdmin/laravel-forestadmin/compare/v1.0.0-beta.4...v1.0.0-beta.5) (2022-03-16)


### Bug Fixes

* **schema:** add support on Mariadb enum ([#27](https://github.com/ForestAdmin/laravel-forestadmin/issues/27)) ([db1d3e6](https://github.com/ForestAdmin/laravel-forestadmin/commit/db1d3e65360308c8544120c308fede02322afd01))
* **schema:** added support enum for mariaDB ([#26](https://github.com/ForestAdmin/laravel-forestadmin/issues/26)) ([9206a3d](https://github.com/ForestAdmin/laravel-forestadmin/commit/9206a3d68b871fed7656316ebb51237328a0a9ab))

# [1.0.0-beta.4](https://github.com/ForestAdmin/laravel-forestadmin/compare/v1.0.0-beta.3...v1.0.0-beta.4) (2022-03-11)


### Bug Fixes

* onboarding installation on php8.1 ([#25](https://github.com/ForestAdmin/laravel-forestadmin/issues/25)) ([ef22634](https://github.com/ForestAdmin/laravel-forestadmin/commit/ef22634059240ad848087d93277fbe5baab4562c))

# [1.0.0-beta.3](https://github.com/ForestAdmin/laravel-forestadmin/compare/v1.0.0-beta.2...v1.0.0-beta.3) (2022-03-10)


### Bug Fixes

* fix dependency psr/simple-cache on installation ([#24](https://github.com/ForestAdmin/laravel-forestadmin/issues/24)) ([3d55fb2](https://github.com/ForestAdmin/laravel-forestadmin/commit/3d55fb230cec5653bd670181604834cbfa1708df))

# [1.0.0-beta.2](https://github.com/ForestAdmin/laravel-forestadmin/compare/v1.0.0-beta.1...v1.0.0-beta.2) (2022-03-09)

# 1.0.0-beta.1 (2022-03-09)


### Features

* initial beta-release ([a5e39cb](https://github.com/ForestAdmin/laravel-forestadmin/commit/a5e39cb2ccb8a5004646894507df5cd0d2e09376))
* **authentication:** add login & callback actions ([#6](https://github.com/ForestAdmin/laravel-forestadmin/issues/6)) ([04edd06](https://github.com/ForestAdmin/laravel-forestadmin/commit/04edd06ab6c63be5180b23f49b46923fa643f795))
* **charts:** add charts support ([#17](https://github.com/ForestAdmin/laravel-forestadmin/issues/17)) ([8d850b1](https://github.com/ForestAdmin/laravel-forestadmin/commit/8d850b1f9766bdfeea3bcca9e7d726d900e16cf3))
* **crud:** create, update & delete actions ([#13](https://github.com/ForestAdmin/laravel-forestadmin/issues/13)) ([ed9e7ce](https://github.com/ForestAdmin/laravel-forestadmin/commit/ed9e7cec29d6cf4dc409d85d6096cce88160719d))
* **crud:** list and show actions [#12](https://github.com/ForestAdmin/laravel-forestadmin/issues/12) ([4cb9a74](https://github.com/ForestAdmin/laravel-forestadmin/commit/4cb9a7438125b7385d4b16530a3015570d4a04ad))
* **filters:** add filters behaviour ([#15](https://github.com/ForestAdmin/laravel-forestadmin/issues/15)) ([bba17bc](https://github.com/ForestAdmin/laravel-forestadmin/commit/bba17bc2c924258a28e835eafbf9bdbe70ca5f09))
* **onboarding:** add new setup command ([#19](https://github.com/ForestAdmin/laravel-forestadmin/issues/19)) ([ac9ac85](https://github.com/ForestAdmin/laravel-forestadmin/commit/ac9ac855504dd01dd9dfbb820ebb4903358389bd))
* **onboarding:** update package to laravel9 ([#21](https://github.com/ForestAdmin/laravel-forestadmin/issues/21)) ([9a4bca1](https://github.com/ForestAdmin/laravel-forestadmin/commit/9a4bca17391e41098b611bf7ee8cf088e607cbba))
* **permission:** added permission layer ([#16](https://github.com/ForestAdmin/laravel-forestadmin/issues/16)) ([9273bc7](https://github.com/ForestAdmin/laravel-forestadmin/commit/9273bc7f9d144d613480d16cb33993ff589127b8))
* **schema:** build schema json file ([#10](https://github.com/ForestAdmin/laravel-forestadmin/issues/10)) ([7e795d8](https://github.com/ForestAdmin/laravel-forestadmin/commit/7e795d8809a17bda0d34a7fce6afc2d990638ce4))
* **scopes:** added scopes support  ([#18](https://github.com/ForestAdmin/laravel-forestadmin/issues/18)) ([59edad6](https://github.com/ForestAdmin/laravel-forestadmin/commit/59edad62a00d22c79e19a15e0ceb7940a4654ea9))
* **search:** add search behaviour ([#14](https://github.com/ForestAdmin/laravel-forestadmin/issues/14)) ([920af78](https://github.com/ForestAdmin/laravel-forestadmin/commit/920af789ed1abfb641d58e660e5f377ae0d9db8c))
* **smart-actions:** added smart-actions support ([#22](https://github.com/ForestAdmin/laravel-forestadmin/issues/22)) ([bdef099](https://github.com/ForestAdmin/laravel-forestadmin/commit/bdef099263cf13206fab85a8c9e314df2d8ce1be))


### BREAKING CHANGES

* package will be available on packagist
