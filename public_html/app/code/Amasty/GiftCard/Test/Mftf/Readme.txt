ReadMeMFTF (recommendations for running tests related to GiftCard extension).

    38 GiftCard specific tests, grouped by purpose, for greater convenience.

            This set of tests is recommended to be run at 2.3.5 magento (highly recommended).

            Tests group: AmGiftCard
            Runs all tests.
                SSH command to run this group of tests:
                vendor/bin/mftf run:group AmGiftCard -r

            Tests group: AmCodePool
                Runs tests related to Code Pool feature.
                SSH command to run this group of tests:
                vendor/bin/mftf run:test AmCodePool -r

            Tests group: AmGiftCodeAccount
                Runs tests related to Gift Code Account feature.
                SSH command to run this group of tests:
                vendor/bin/mftf run:test AmGiftCodeAccount -r

            Tests group: AmApplyGiftCard
                Runs tests related to Applying Gift Code Account.
                SSH command to run this group of tests:
                vendor/bin/mftf run:test AmApplyGiftCard -r

            Tests group: AmGiftCardCheckout
                Runs tests related to operations with Gift Code Account on checkout.
                SSH command to run this group of tests:
                vendor/bin/mftf run:test AmGiftCardCheckout -r

            Tests group: AmGiftCardShoppingCart
                Runs tests related to operations with Gift Code Account on shopping cart.
                SSH command to run this group of tests:
                vendor/bin/mftf run:test AmGiftCardShoppingCart -r

            Tests group: AmGiftCardOnCustomerAccount
                Runs tests related to operations with Gift Code Account on customer account.
                SSH command to run this group of tests:
                vendor/bin/mftf run:test AmGiftCardOnCustomerAccount -r

            Tests group: AmGiftCardProduct
                Runs tests related to operations with Gift Card Product.
                SSH command to run this group of tests:
                vendor/bin/mftf run:test AmGiftCardProduct -r

            Here and below:
            to run groups of tests related to GiftCard extension, it is necessary to add a CSV file with the name
            "codePool.csv" to the folder "magento/dev/tests/acceptance/tests/_data". File should contain 10 records:
            codes that match Codes Template "defaultCodePool_{L}{L}{D}{D}{D}".
