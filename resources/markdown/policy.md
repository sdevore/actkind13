# Privacy

Ad-supported businesses often track a lot of information about you to better serve advertisers. ActKind.online isn’t
like that. We have no ads and the business model is a simple donation based service where you give to help support this
community. We only collect enough information to run ActKind.online.

This help page outlines what kind of data is used by each major component of ActKind.online. It will be routinely
updated and the version history for the entire help site is available on GitHub.

## ActKind.online platform

You register with a name, email address, username. We store this information in a database on the ActKind.online
servers. We send email to your address such as donation receipts or other occasional notifications. If you do not upload
a profile photo, we use a hash of your email address to look up a profile photo on Gravatar.com if you have provided one
there.

ActKind.online does store a [hash](link/to/information/about/hashes) of your password so we can never recover it or send
it to you, all you will be able to do is to reset it. How the password is hashed may change as the standard for this
practice changes. There are also tokens on your account that may be used with third-party apps. We
use [cookies](link/to/information/about/cookies) to keep track of when you are signed in to ActKind.online.

Your time zone and IP address are sent from a web browser when visiting ActKind.online. We store the time zone with your
account and use it to adjust all the post times to your local time. We use the IP address to rate-limit a client when
there are too many requests in a short time period.

If you provide your location with an act of kindness entry this is stored, but it's display is `fuzzed` to different
extents depending on who is viewing that data and the privacy levels that you have selected for that posting. We do not
attempt to ascertain location from ip address or other sources only if you explicitly provide it. We are looking at
other ways of gradually `fuzzing` or degrading the details of location information as time goes by.

We are planning on providing tools that you can use to download any information related to your account. As well as
providing way for you remove yourself completely when you choose to move on and delete your account. This would include
your personal information as well as any acts you have entered, your comments and likes if you should choose to.

## Credit cards

ActKind.online uses Stripe for credit card processing. Credit card information is sent directly from your web browser to
Stripe. ActKind.online does not see or store your credit card number.

Stripe manages your billing information, including name, zip code, credit card number, and expiration date. Your
ActKind.online email address and username are stored on Stripe for sending receipt emails, and so that we can look up
your account to confirm or update something about your donations, usually from a support request, or to thank you for
helping to support us.
