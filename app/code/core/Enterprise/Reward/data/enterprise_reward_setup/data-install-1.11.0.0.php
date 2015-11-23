<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition End User License Agreement
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magento.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Enterprise
 * @package     Enterprise_Reward
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */


$cmsPage = array(
    'title'           => 'Reward Points',
    'root_template'   => 'one_column',
    'identifier'      => 'reward-points',
    'content_heading' => 'Reward Points',
    'is_active'       => 1,
    'stores'          => array(0),
    'content' => '<p>The Reward Points Program allows you to earn points for certain actions you take on the site. Points are awarded based on making purchases and customer actions such as submitting reviews.</p>

<h2>Benefits of Reward Points for Registered Customers</h2>
<p>Once you register you will be able to earn and accrue reward points, which are then redeemable at time of purchase towards the cost of your order. Rewards are an added bonus to your shopping experience on the site and just one of the ways we thank you for being a loyal customer.</p>

<h2>Earning Reward Points</h2>
<p>Rewards can currently be earned for the following actions:</p>
<ul>
<li>Making purchases — every time you make a purchase you earn points based on the price of products purchased and these points are added to your Reward Points balance.</li>
<li>Registering on the site.</li>
<li>Subscribing to a newsletter for the first time.</li>
<li>Sending Invitations — Earn points by inviting your friends to join the site.</li>
<li>Converting Invitations to Customer — Earn points for every invitation you send out which leads to your friends registering on the site.</li>
<li>Converting Invitations to Order — Earn points for every invitation you send out which leads to a sale.</li>
<li>Review Submission — Earn points for submitting product reviews.</li>
<li>New Tag Submission — Earn points for adding tags to products.</li>
</ul>

<h2>Reward Points Exchange Rates</h2>
<p>The value of reward points is determined by an exchange rate of both currency spent on products to points, and an exchange rate of points earned to currency for spending on future purchases.</p>

<h2>Redeeming Reward Points</h2>
<p>You can redeem your reward points at checkout. If you have accumulated enough points to redeem them you will have the option of using points as one of the payment methods.  The option to use reward points, as well as your balance and the monetary equivalent this balance, will be shown to you in the Payment Method area of the checkout.  Redeemable reward points can be used in conjunction with other payment methods such as credit cards, gift cards and more.</p>
<p><img src="{{skin url="images/reward_points/payment.gif"}}" alt="Payment Information" /></p>

<h2>Reward Points Minimums and Maximums</h2>
<p>Reward points may be capped at a minimum value required for redemption.  If this option is selected you will not be able to use your reward points until you accrue a minimum number of points, at which point they will become available for redemption.</p>
<p>Reward points may also be capped at the maximum value of points which can be accrued. If this option is selected you will need to redeem your accrued points before you are able to earn more points.</p>

<h2>Managing My Reward Points</h2>
<p>You have the ability to view and manage your points through your <a href="{{store url="customer/account"}}">Customer Account</a>. From your account you will be able to view your total points (and currency equivalent), minimum needed to redeem, whether you have reached the maximum points limit and a cumulative history of points acquired, redeemed and lost. The history record will retain and display historical rates and currency for informational purposes. The history will also show you comprehensive informational messages regarding points, including expiration notifications.</p>
<p><img src="{{skin url="images/reward_points/my_account.gif"}}" alt="My Account" /></p>

<h2>Reward Points Expiration</h2>
<p>Reward points can be set to expire. Points will expire in the order form which they were first earned.</p>
<p><strong>Note</strong>: You can sign up to receive email notifications each time your balance changes when you either earn, redeem or lose points, as well as point expiration notifications. This option is found in the <a href="{{store url="reward/customer/info"}}">Reward Points section</a> of the My Account area.</p>
',
);

Mage::getModel('cms/page')->setData($cmsPage)->save();
