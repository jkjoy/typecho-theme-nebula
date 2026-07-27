<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<section class="comments" id="comments">
    <?php $this->comments()->to($comments); ?>
    <div class="comments-heading">
        <div><p class="section-kicker">CONVERSATION</p><h2><?php $this->commentsNum('期待第一条评论', '1 条评论', '%d 条评论'); ?></h2></div>
    </div>

    <?php if ($comments->have()): ?>
        <ul class="comment-list">
            <?php $comments->listComments(['before' => '', 'after' => '', 'avatarSize' => 48, 'defaultAvatar' => 'mp']); ?>
        </ul>
        <?php $comments->pageNav('上一页', '下一页', 3, '…', ['wrapTag' => 'nav', 'wrapClass' => 'pager comment-pager', 'itemTag' => 'span', 'textTag' => 'span', 'currentClass' => 'current']); ?>
    <?php endif; ?>

    <?php if ($this->allow('comment')): ?>
        <div id="<?php $this->respondId(); ?>" class="respond">
            <div class="cancel-comment-reply"><?php $comments->cancelReply('取消回复'); ?></div>
            <h3 id="response">留下你的想法</h3>
            <form method="post" action="<?php $this->commentUrl(); ?>" id="comment-form" class="comment-form" role="form">
                <?php if ($this->user->hasLogin()): ?>
                    <p class="logged-in">当前以 <a href="<?php $this->options->profileUrl(); ?>"><?php $this->user->screenName(); ?></a> 登录 · <a href="<?php $this->options->logoutUrl(); ?>">退出</a></p>
                <?php else: ?>
                    <div class="form-row">
                        <label><span>称呼</span><input type="text" name="author" value="<?php $this->remember('author'); ?>" autocomplete="name" required></label>
                        <label><span>邮箱<?php if (!$this->options->commentsRequireMail): ?>（选填）<?php endif; ?></span><input type="email" name="mail" value="<?php $this->remember('mail'); ?>" autocomplete="email"<?php if ($this->options->commentsRequireMail): ?> required<?php endif; ?>></label>
                        <label><span>网站<?php if (!$this->options->commentsRequireUrl): ?>（选填）<?php endif; ?></span><input type="url" name="url" value="<?php $this->remember('url'); ?>" autocomplete="url" placeholder="https://"<?php if ($this->options->commentsRequireUrl): ?> required<?php endif; ?>></label>
                    </div>
                <?php endif; ?>
                <label class="textarea-label"><span>评论内容</span><textarea name="text" rows="6" required><?php $this->remember('text'); ?></textarea></label>
                <button class="btn submit-comment" type="submit">提交评论 <span aria-hidden="true">→</span></button>
            </form>
        </div>
    <?php else: ?>
        <p class="comments-closed">评论已关闭</p>
    <?php endif; ?>
</section>
