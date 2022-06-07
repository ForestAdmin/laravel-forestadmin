module.exports = {
  branches: ["main", { name: "beta", channel: "beta", prerelease: true }],
  plugins: [
    [
      "@semantic-release/commit-analyzer",
      {
        releaseRules: [
          // This rule allow to force a release by adding "force-release" in scope.
          // Example: `chore(force-release): support new feature`
          // Source: https://github.com/semantic-release/commit-analyzer#releaserules
          { scope: "force-release", release: "patch" },
        ],
      },
    ],
    "@semantic-release/release-notes-generator",
    "@semantic-release/changelog",
    [
      "@semantic-release/exec",
      {
        prepareCmd:
          'sed -i "s/LIANA_VERSION = \'.*\'/LIANA_VERSION = \'${nextRelease.version}\'/g" src/Schema/Schema.php; sed -i \'s/"version": ".*"/"version": "${nextRelease.version}"/g\' composer.json; sed -i \'s/"version": ".*"/"version": "${nextRelease.version}"/g\' package.json;',
      },
    ],
    [
      "@semantic-release/git",
      {
        assets: [
          "CHANGELOG.md",
          "composer.json",
          "package.json",
          "src/Schema/Schema.php",
        ],
      },
    ],
    "@semantic-release/github",
    [
      "semantic-release-slack-bot",
      {
        markdownReleaseNotes: true,
        notifyOnSuccess: true,
        notifyOnFail: false,
        onSuccessTemplate: {
          text: "📦 $package_name@$npm_package_version has been released!",
          blocks: [
            {
              type: "section",
              text: {
                type: "mrkdwn",
                text: "*New `$package_name` package released!*",
              },
            },
            {
              type: "context",
              elements: [
                {
                  type: "mrkdwn",
                  text: "📦  *Version:* <$repo_url/releases/tag/v$npm_package_version|$npm_package_version>",
                },
              ],
            },
            {
              type: "divider",
            },
          ],
          attachments: [
            {
              blocks: [
                {
                  type: "section",
                  text: {
                    type: "mrkdwn",
                    text: "*Changes* of version $release_notes",
                  },
                },
              ],
            },
          ],
        },
        packageName: "laravel-forestadmin",
      },
    ],
  ],
};
