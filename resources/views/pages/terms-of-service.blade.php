<x-layouts.simple>

        <x-slot name="title">
            {{ __('Terms of Service') }}
        </x-slot>

        <x-heading.h1 class="md:!text-4xl !text-4xl pt-4 pb-6">
            {{ __('Terms of Service') }}
        </x-heading.h1>

        <p class="mb-6">
        Please read these Terms of Service (“Terms”, “Terms of Service”) carefully before using the {{ config('app.url', '') }} website (the “Service”) operated by {{ config('app.name', 'SaaSykit') }} (“us”, “we”, or “our”).
        </p>

        <p class="mb-6">
        Your access to and use of the Service is conditioned upon your acceptance of and compliance with these Terms. These Terms apply to all visitors, users, and others who wish to access or use the Service.
        By accessing or using the Service, you agree to be bound by these Terms. If you disagree with any part of the terms, then you do not have permission to access the Service.
        <p class="mb-6">

        <x-heading.h2 class="text-xl mb-2">
        Communications
        </x-heading.h2>

        <p class="mb-6">
        By creating an Account on our service, you agree to subscribe to newsletters, marketing or promotional materials, and other information we may send. However, you may opt out of receiving any, or all, of these communications from us by following the unsubscribe link or instructions provided in any email we send.
        </p>

        <x-heading.h2 class="text-xl mb-2">
        Subscriptions
        </x-heading.h2>

        <p class="mb-6">
        Some parts of the Service are billed on a subscription basis (“Subscription(s)”). You will be billed in advance on a recurring and periodic basis (“Billing Cycle”). Billing cycles are set either on a monthly or annual basis, depending on the type of subscription plan you select when purchasing a Subscription.
        At the end of each Billing Cycle, your Subscription will automatically renew under the exact same conditions unless you cancel it or {{ config('app.name', 'SaaSykit') }} cancels it. You may cancel your Subscription renewal by contacting {{ config('app.name', 'SaaSykit') }} customer support team or by canceling the Subscription from your account dashboard.
        </p>

        <p class="mb-6">
        A valid payment method, including credit card, is required to process the payment for your Subscription. You shall provide {{ config('app.name', 'SaaSykit') }} with accurate and complete billing information, including full name, address, state, zip code, telephone number, and valid payment method information. By submitting such payment information, you automatically authorize {{ config('app.name', 'SaaSykit') }} to charge all Subscription fees incurred through your account to any such payment instruments.
        Should automatic billing fail to occur for any reason, {{ config('app.name', 'SaaSykit') }} will issue an electronic invoice indicating that you must proceed manually, within a certain deadline date, with the full payment corresponding to the billing period as indicated on the invoice.
        </p>

        <x-heading.h2 class="text-xl mb-2">
        Free Trial
        </x-heading.h2>

        <p class="mb-6">
        {{ config('app.name', 'SaaSykit') }} may, at its sole discretion, offer a Subscription with a free trial for a limited period of time (“Free Trial”).
        You may be required to enter your billing information in order to sign up for the Free Trial.
        If you do enter your billing information when signing up for the Free Trial, you will not be charged by {{ config('app.name', 'SaaSykit') }} until the Free Trial has expired. On the last day of the Free Trial period, unless you canceled your Subscription, you will be automatically charged the applicable Subscription fees for the type of Subscription you have selected.
        At any time and without notice, {{ config('app.name', 'SaaSykit') }} reserves the right to (i) modify the terms and conditions of the Free Trial offer, or (ii) cancel such Free Trial offer.
        </p>

        <x-heading.h2 class="text-xl mb-2">
        Fee Changes
        </x-heading.h2>

        <p class="mb-6">
        {{ config('app.name', 'SaaSykit') }}, in its sole discretion and at any time, may modify the Subscription fees for the Subscriptions. Any Subscription fee change will become effective at the end of the then-current Billing Cycle.
        </p>

        <p class="mb-6">
        {{ config('app.name', 'SaaSykit') }} will provide you with reasonable prior notice of any change in Subscription fees to give you an opportunity to terminate your Subscription before such change becomes effective.
        </p>

        <p class="mb-6">
        Your continued use of the Service after the Subscription fee change comes into effect constitutes your agreement to pay the modified Subscription fee amount.
        </p>


        <x-heading.h2 class="text-xl mb-2">
        Refunds
        </x-heading.h2>

        <p class="mb-6">
        Certain refund requests for Subscriptions may be considered by {{ config('app.name', 'SaaSykit') }} on a case-by-case basis and granted in the sole discretion of {{ config('app.name', 'SaaSykit') }}.
        </p>

        <x-heading.h2 class="text-xl mb-2">
        Content
        </x-heading.h2>

        <p class="mb-6">
        Our Service allows you to post, link, store, share, and otherwise make available certain information, text, graphics, videos, or other material (“Content”). You are responsible for the Content that you post on or through the Service, including its legality, reliability, and appropriateness.
        By posting Content on or through the Service, you represent and warrant that: (1) the Content is yours (you own it) and/or you have the right to use it and the right to grant us the rights and license as provided in these Terms, and (2) that the posting of your Content on or through the Service does not violate the privacy rights, publicity rights, copyrights, contract rights, or any other rights of any person or entity. We reserve the right to terminate the account of anyone found to be infringing on a copyright.
        </p>

        <p class="mb-6">
        You retain any and all of your rights to any Content you submit, post or display on or through the Service, and you are responsible for protecting those rights. We take no responsibility and assume no liability for Content you or any third party posts on or through the Service. However, by posting Content using the Service, you grant us the right and license to use, modify, publicly perform, publicly display, reproduce, and distribute such Content on and through the Service. You agree that this license includes the right for us to make your Content available to other users of the Service, who may also use your Content subject to these Terms.
        </p>

        <p class="mb-6">
        {{ config('app.name', 'SaaSykit') }} has the right but not the obligation to monitor and edit all Content provided by users.
        </p>

        <p class="mb-6">
        In addition, Content found on or through this Service is the property of {{ config('app.name', 'SaaSykit') }} or used with permission. You may not distribute, modify, transmit, reuse, download, repost, copy, or use said Content, whether in whole or in part, for commercial purposes or for personal gain, without express advance written permission from us.
        </p>

        <x-heading.h2 class="text-xl mb-2">
        Accounts
        </x-heading.h2>

        <p class="mb-6">
        When you create an account with us, you guarantee that you are above the age of 18, and that the information you provide us is accurate, complete, and current at all times. Inaccurate, incomplete, or obsolete information may result in the immediate termination of your account on the Service.
        You are responsible for maintaining the confidentiality of your account and password, including but not limited to the restriction of access to your computer and/or account. You agree to accept responsibility for any and all activities or actions that occur under your account and/or password, whether your password is with our Service or a third-party service. You must notify us immediately upon becoming aware of any breach of security or unauthorized use of your account.
        </p>

        <p class="mb-6">
        You may not use as a username the name of another person or entity or that is not lawfully available for use, a name or trademark that is subject to any rights of another person or entity other than you, without appropriate authorization. You may not use as a username any name that is offensive, vulgar or obscene.
        We reserve the right to refuse service, terminate accounts, remove or edit content in our sole discretion.
        </p>

        <x-heading.h2 class="text-xl mb-2">
        Intellectual Property
        </x-heading.h2>

        <p class="mb-6">
        The Service and its original content (excluding Content provided by users), features and functionality are and will remain the exclusive property of {{ config('app.name', 'SaaSykit') }} and its licensors. The Service is protected by copyright, trademark, and other laws of both the United States and foreign countries. Our trademarks and trade dress may not be used in connection with any product or service without the prior written consent of {{ config('app.name', 'SaaSykit') }}.
        </p>

        <x-heading.h2 class="text-xl mb-2">
        Links To Other Web Sites
        </x-heading.h2>

        <p class="mb-6">
        Our Service may contain links to third-party websites or services that are not owned or controlled by {{ config('app.name', 'SaaSykit') }}.
        {{ config('app.name', 'SaaSykit') }} has no control over, and assumes no responsibility for the content, privacy policies, or practices of any third-party websites or services. We do not warrant the offerings of any of these entities/individuals or their websites
        </p>

        <p class="mb-6">
        You acknowledge and agree that {{ config('app.name', 'SaaSykit') }} shall not be responsible or liable, directly or indirectly, for any damage or loss caused or alleged to be caused by or in connection with the use of or reliance on any such content, goods, or services available on or through any such third-party websites or services.
        </p>

        <p class="mb-6">
        We strongly advise you to read the terms and conditions and privacy policies of any third-party websites or services that you visit.
        </p>

        <x-heading.h2 class="text-xl mb-2">
        Termination
        </x-heading.h2>

        <p class="mb-6">
        We may terminate or suspend your account and bar access to the Service immediately, without prior notice or liability, under our sole discretion, for any reason whatsoever and without limitation, including but not limited to a breach of the Terms.
        If you wish to terminate your account, you may simply discontinue using the Service.
        </p>

        <p class="mb-6">
        All provisions of the Terms which by their nature should survive termination shall survive termination, including, without limitation, ownership provisions, warranty disclaimers, indemnity, and limitations of liability.
        </p>

        <x-heading.h2 class="text-xl mb-2">
        Limitation Of Liability
        </x-heading.h2>

        <p class="mb-6">
        In no event shall {{ config('app.name', 'SaaSykit') }}, nor its directors, employees, partners, agents, suppliers, or affiliates, be liable for any indirect, incidental, special, consequential, or punitive damages, including without limitation, loss of profits, data, use, goodwill, or other intangible losses, resulting from (i) your access to or use of or inability to access or use the Service; (ii) any conduct or content of any third party on the Service; (iii) any content obtained from the Service; and (iv) unauthorized access, use, or alteration of your transmissions or content, whether based on warranty, contract, tort (including negligence), or any other legal theory, whether or not we have been informed of the possibility of such damage, and even if a remedy set forth herein is found to have failed of its essential purpose.
        </p>

        <x-heading.h2 class="text-xl mb-2">
        Disclaimer
        </x-heading.h2>

        <p class="mb-6">
        Your use of the Service is at your sole risk. The Service is provided on an “AS IS” and “AS AVAILABLE” basis. The Service is provided without warranties of any kind, whether express or implied, including, but not limited to, implied warranties of merchantability, fitness for a particular purpose, non-infringement, or course of performance.
        </p>

        <p class="mb-6">
        {{ config('app.name', 'SaaSykit') }} its subsidiaries, affiliates, and its licensors do not warrant that a) the Service will function uninterrupted, secure, or available at any particular time or location; b) any errors or defects will be corrected; c) the Service is free of viruses or other harmful components; or d) the results of using the Service will meet your requirements.
        </p>

        <x-heading.h2 class="text-xl mb-2">
        Exclusions
        </x-heading.h2>

        <p class="mb-6">
        Some jurisdictions do not allow the exclusion of certain warranties or the exclusion or limitation of liability for consequential or incidental damages, so the limitations above may not apply to you.
        </p>

        <x-heading.h2 class="text-xl mb-2">
        Changes
        </x-heading.h2>

        <p class="mb-6">
        We reserve the right, at our sole discretion, to modify or replace these Terms at any time. If a revision is material, we will provide at least 30 days notice prior to any new terms taking effect. What constitutes a material change will be determined at our sole discretion.
        </p>

        <p class="mb-6">
        By continuing to access or use our Service after any revisions become effective, you agree to be bound by the revised terms. If you do not agree to the new terms, you are no longer authorized to use the Service.
        </p>

        <x-heading.h2 class="text-xl mb-2">
        Contact Us
        </x-heading.h2>

        <p class="mb-6">
            If you have any questions about these Terms, please contact us at <a href="mailto:{{ config('app.support_email', '') }}">{{ config('app.support_email', '') }}</a>.
        </p>


</x-layouts.simple>
