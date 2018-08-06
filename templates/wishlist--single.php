<?php

/**
 * Template for output of a single wishlist
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

$settings = get_option('wpgr_settings');

?>

<section class="wpgr-wishlist" data-id="<?= $wishlist_id ?>">
    <div class="wpgr-wishlist__inner">
        <?php

            foreach ( $wishlist as $gift ):
                $is_available = $gift['gift_availability'] == 'true';
                $has_buyer = !empty($gift['gift_reserver']);
                $raw_gift_price = !empty( $gift['gift_price'] ) ? $gift['gift_price'] : 0;
                $gift_price = $raw_gift_price != 0 ? number_format_i18n( $raw_gift_price ) : '';
                $has_parts = isset($gift['gift_has_parts']) && $gift['gift_has_parts'] == 'true';
                $gift_parts = $has_parts ? $gift['gift_parts_total'] : 1;
                $reserved_parts = static::get_reserved_parts($wishlist_id, $gift['gift_id']);
                $price_per_part = intval($raw_gift_price) / intval($gift_parts);

                // legacy variable when we tried to put gift parts into multiple steps in the popup
                $is_single = true; // needs to be replaced

                $classes = array('wpgr-m_card');

                if ( !$is_available ) {
                    $classes[] = 'wpgr-m_card--bought';
                }
                if ( $is_single ) {
                    $classes[] = 'wpgr-m_card--single';
                }
                if ( empty($gift['gift_description']) ) {
                    $classes[] = 'wpgr-m_card--nocontent';
                } else {
                    $classes[] = 'wpgr-m_card--content';
                }
            ?>

            <div
                class="<?= implode(' ', $classes) ?>"
                data-wish-id="<?= $gift['gift_id'] ?>"
                data-parts="<?= $gift_parts ?>"
                data-parts-given="<?= $reserved_parts ?>"
                data-price-per-part="<?= $price_per_part ?>"
                data-currency="<?= $currency ?>"
                data-currency-placement="<?= $currency_placement ?>">

            <?php /* PRICE_LABEL */  ?>
            <?php if ( !empty($gift_price) ): ?>
                <div class="wpgr-m_card__price-wrapper">
                    <p class="wpgr-m_card__price">
                        <?= $currency_placement === 'before' ? $currency . $gift_price : $gift_price . $currency ?>
                    </p>
                    <?php if ( !$is_single ): ?>
                        <p class="wpgr-m_card__price-text">
                        <?php
                            /* translators: (price per) each part of the gift */
                            echo __('each', 'wpgiftregistry');
                        ?>
                        </p>
                    <?php endif; ?>
                    <i class="wpgr-m_card__price-icon"></i>
                </div>
            <?php endif; ?>

            <?php /* CARD_IMAGE */  ?>
            <?php if ( !empty($gift['gift_url']) ): ?>
                <a class="wpgr-m_card__figure-anchor" href="<?= static::transform_to_affiliate_link( $gift['gift_url'] ) ?>" target="_blank">
            <?php endif; ?>
                    <div class="wpgr-m_card__figure-wrapper">
                        <div class="wpgr-m_card__figure" <?= empty($gift['gift_image']) ? '' : "style='background-image:url(" . $gift['gift_image'] . ")'" ?>></div>
                    </div>
            <?php if ( !empty($gift['gift_url']) ): ?>
                </a>
            <?php endif; ?>

            <?php /* CARD_HEADING */  ?>
            <div class="wpgr-m_card__heading-wrapper">
                <?php if ( !empty($gift['gift_title']) ): ?>
                    <h4 class="wpgr-m_card__heading"><?= $gift['gift_title'] ?></h4>
                <?php endif; ?>
            </div>

            <?php /* CARD_CONTENT */  ?>
            <div class="wpgr-m_card__content">
                <?php if ( !empty($gift['gift_description']) ): ?>
                    <p class="wpgr-m_card__desc"><?= $gift['gift_description'] ?></p>
                <?php endif; ?>
                <div class="wpgr-m_card__btn-wrapper">
                    <?php if ( $has_parts ): ?>
                        <div class="wpgr-m_card__progress-wrapper">
                            <div class="wpgr-m_card__progress">
                                <span style="width: <?= ($reserved_parts / $gift['gift_parts_total']) * 100 ?>%;"></span>
                            </div>
                            <span><?= $reserved_parts ?></span> / <?= $gift['gift_parts_total'] ?>
                        </div>
                    <?php endif; ?>

                    <?php if ( !empty($gift['gift_url']) ): ?>
                        <a class="wpgr-m_card__btn wpgr-m_btn" target="_blank" href="<?= static::transform_to_affiliate_link( $gift['gift_url'] ) ?>">
                            <span class="wpgr-m_card__btn-text"><?= __('View', 'wpgiftregistry') ?></span>
                            <?php /* <i class="wpgr-m_card__btn-icon wpgr-m_card__btn-icon--view"></i> */ ?>
                        </a>
                    <?php endif; ?>
                    <button class="wpgr-m_card__btn wpgr-m_btn wpgr-m_btn__open" type="button" name="button">
                        <span class="wpgr-m_card__btn-text"><?= !$has_parts ? __('Give', 'wpgiftregistry') : __('Give Part', 'wpgiftregistry') ?></span>
                        <?php /* <i class="wpgr-m_card__btn-icon wpgr-m_card__btn-icon--give"></i> */ ?>
                    </button>
                </div>
            </div>

            <?php /* CARD_TOGGLE */  ?>
            <?php if ( !empty($gift['gift_description']) ): ?>
                <div class="wpgr-m_card__toggle">
                    <i class="wpgr-m_card__toggle-icon"></i>
                </div>
            <?php endif; ?>
        </div>
    <?php
        endforeach;
    ?>

        <form id="wpgr_popup" class="wpgr-o_popup wpgr-o_popup--single wpgr-o_popup__form">

            <div id="wpgr_popup_name" class="wpgr-o_popup__step wpgr-o_popup__step--1">
                <header class="wpgr-o_popup__header">
                    <p class="wpgr-o_popup__question"><?= __('Mark gift as reserved?', 'wpgiftregistry') /* Geschenk reservieren? */ ?></p>
                </header>

                <div class="wpgr-o_popup__parts">
                    <p class="wpgr-o_popup__desc">How much do you want to give? Translate this</p>
                    <div class="wpgr-o_popup__rangeslider-wrapper">
                        <?php // Input "each" translation here ?>
                        <div class="wpgr-o_popup__rangeslider-parts" data-part="part" data-parts="parts">
                        </div>
                        <input
                            id="no_of_parts"
                            name="no_of_parts"
                            class="wpgr-o_popup__rangeslider"
                            type="range"
                            max="10"
                            data-orientation="horizontal"
                        >
                        <div class="wpgr-o_popup__rangeslider-result">
                            <span class="wpgr-o_popup__rangeslider-val"></span> / 10
                        </div>
                    </div>
                </div>

                <p class="wpgr-o_popup__desc"><?= __('Leave your name for the recipient:', 'wpgiftregistry') /* Lasse den Beschenkten wissen, dass das Geschenk von Dir ist: */ ?></p>
                <div class="wpgr-o_popup__input-wrapper">
                    <label class="wpgr-o_popup__input-label" for="your_name2"><?= __('Your name', 'wpgiftregistry') /* Dein Name */ ?></label>
                    <input id="your_name2" name="your_name2" class="wpgr-o_popup__input-text" type="text">
                </div>
                <?php if ( isset($settings['show_email_field']) && $settings['show_email_field'] ): ?>
                    <div class="wpgr-o_popup__input-wrapper">
                        <label class="wpgr-o_popup__input-label" for="your_name2"><?= __('Your email', 'wpgiftregistry') ?></label>
                        <input id="your_email" name="your_email" class="wpgr-o_popup__input-text" type="text">
                    </div>
                <?php endif; ?>
                <?php if ( isset($settings['show_message_field']) && $settings['show_message_field'] ): ?>
                    <div class="wpgr-o_popup__input-wrapper">
                        <label class="wpgr-o_popup__input-label" for="your_message"><?= __('Personal message', 'wpgiftregistry') ?></label>
                        <textarea id="your_message" name="your_message" class="wpgr-o_popup__input-textarea"></textarea>
                    </div>
                <?php endif; ?>
                <div class="wpgr-o_popup__btn-wrapper">
                    <button class="wpgr-o_popup__btn-prev wpgr-m_btn"><?= __('Cancel', 'wpgiftregistry') /* Abbrechen */ ?></button>
                    <input class="wpgr-o_popup__btn-save wpgr-m_btn wpgr-m_btn--next" type="submit" name="confirm" value="<?= __('Confirm', 'wpgiftregistry') /* Bestätigen */ ?>">
                </div>

                <button class="wpgr-o_popup__btn-close wpgr-m_btn-close">
                    <i class="wpgr-m_btn-close-icon"></i>
                </button>
            </div>

        </form>
    </div>
</section>
