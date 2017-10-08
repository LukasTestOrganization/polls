# Polls

This is a poll app, similar to doodle or dudle, for Nextcloud / ownCloud written in PHP and JS / jQuery.
It is a rework of the already existing [polls app](https://github.com/raduvatav/polls) written by @raduvatav.

### Features

- :bar_chart: Create / edit polls (datetimes _and_ texts)
- :date: Set expiration date
- :lock: Restrict access (only logged in users, certain groups / users, hidden and public)
- :speech_balloon: Comments

### Bugs

- https://github.com/nextcloud/polls/issues

### Screenshots

![New poll](https://github.com/nextcloud/polls/blob/master/screenshots/new-poll.png)
![Overview](https://github.com/nextcloud/polls/blob/master/screenshots/overview.png)
![Vote](https://github.com/nextcloud/polls/blob/master/screenshots/vote.png)

## Installation / Update

This app is supposed to work on Nextcloud version 11+ or ownCloud version 8+.

### Install latest release

You can download and install the latest release from the [Nextcloud app store](https://apps.nextcloud.com/apps/polls).

### Install from git

If you want to run the latest development version from git source, you need to clone the repo to your apps folder:

```
git clone https://github.com/nextcloud/polls.git
```

## Contribution Guidelines

Please read the [Code of Conduct](https://nextcloud.com/community/code-of-conduct/). This document offers some guidance 
to ensure Nextcloud participants can cooperate effectively in a positive and inspiring atmosphere, and to explain how together 
we can strengthen and support each other.

For more information please review the [guidelines for contributing](https://github.com/nextcloud/server/blob/master/CONTRIBUTING.md) to this repository.

### Apply a license

All contributions to this repository are considered to be licensed under
the GNU AGPLv3 or any later version.

Contributors to the Polls app retain their copyright. Therefore we recommend
to add following line to the header of a file, if you changed it substantially:

```
@copyright Copyright (c) <year>, <your name> (<your email address>)
```

For further information on how to add or update the license header correctly please have a look at [our licensing HowTo][applyalicense].

### Sign your work

We use the Developer Certificate of Origin (DCO) as a additional safeguard
for the Nextcloud project. This is a well established and widely used
mechanism to assure contributors have confirmed their right to license
their contribution under the project's license.
Please read [developer-certificate-of-origin][dcofile].
If you can certify it, then just add a line to every git commit message:

````
  Signed-off-by: Random J Developer <random@developer.example.org>
````

Use your real name (sorry, no pseudonyms or anonymous contributions).
If you set your `user.name` and `user.email` git configs, you can sign your
commit automatically with `git commit -s`. You can also use git [aliases](https://git-scm.com/book/tr/v2/Git-Basics-Git-Aliases)
like `git config --global alias.ci 'commit -s'`. Now you can commit with
`git ci` and the commit will be signed.

[dcofile]: https://github.com/nextcloud/server/blob/master/contribute/developer-certificate-of-origin
[applyalicense]: https://github.com/nextcloud/server/blob/master/contribute/HowToApplyALicense.md
