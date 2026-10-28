<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php
if (!function_exists('threadedComments')) {
    /**
     * 自定义评论列表
     */
    function threadedComments($comments, $options)
    {
        $commentClass = '';
        if ($comments->authorId) {
            $commentClass .= $comments->authorId == $comments->ownerId ? ' by-author' : ' by-user';
        }
        ?>
        <li id="<?php $comments->theId(); ?>" class="comment-body<?php echo $commentClass; ?><?php echo $comments->levels > 0 ? ' is-child' : ''; ?>">
            <div class="comment-avatar"><?php sleek_gravatar($comments, 38); ?></div>
            <div class="comment-main">
                <div class="comment-head">
                    <span class="comment-name"><?php $comments->author(); ?></span>
                    <?php if ($comments->authorId && $comments->authorId == $comments->ownerId): ?>
                        <span class="badge-owner"><?php _e('博主'); ?></span>
                    <?php endif; ?>
                    <?php if ($comments->status != 'approved'): ?>
                        <span class="badge-pending"><?php _e('待审核'); ?></span>
                    <?php endif; ?>
                    <time class="comment-time" datetime="<?php $comments->date('c'); ?>"><?php $comments->date('Y-m-d H:i'); ?></time>
                </div>
                <div class="comment-text"><?php $comments->content(); ?></div>
                <div class="comment-actions"><?php $comments->reply(_t('回复')); ?></div>
                <?php if ($comments->children): ?>
                    <ol class="comment-children"><?php $comments->threadedComments(); ?></ol>
                <?php endif; ?>
            </div>
        </li>
        <?php
    }
}
?>
<?php $this->comments()->to($comments); ?>

<div class="comments-head">
    <h2 class="comments-title">
        <?php $this->commentsNum(_t('评论'), _t('1 条评论'), _t('%d 条评论')); ?>
    </h2>
    <span class="comments-sub"><?php _e('友善交流，好好说话'); ?></span>
</div>

<?php if ($comments->have()): ?>
    <?php $comments->listComments(); ?>
    <div class="pager comments-pager"><?php $comments->pageNav('上一页', '下一页'); ?></div>
<?php else: ?>
    <p class="comments-empty"><?php _e('还没有评论，来说两句吧。'); ?></p>
<?php endif; ?>

<?php if ($this->allow('comment')): ?>
    <div id="<?php $this->respondId(); ?>" class="respond">
        <div class="respond-head">
            <h3 id="response"><?php _e('写评论'); ?></h3>
            <div class="cancel-reply"><?php $comments->cancelReply(); ?></div>
        </div>

        <form method="post" action="<?php $this->commentUrl(); ?>" id="comment-form" class="comment-form">
            <?php if ($this->user->hasLogin()): ?>
                <p class="logged-in">
                    <?php _e('登录身份'); ?>：
                    <a href="<?php $this->options->profileUrl(); ?>"><?php $this->user->screenName(); ?></a>
                    <span class="dot">·</span>
                    <a href="<?php $this->options->logoutUrl(); ?>"><?php _e('退出'); ?></a>
                </p>
            <?php else: ?>
                <div class="form-grid">
                    <label class="field">
                        <span class="field-label"><?php _e('称呼'); ?> <em>*</em></span>
                        <input type="text" name="author" id="author" value="<?php $this->remember('author'); ?>" placeholder="<?php _e('怎么称呼你'); ?>" required>
                    </label>
                    <label class="field">
                        <span class="field-label"><?php _e('邮箱'); ?><?php if ($this->options->commentsRequireMail): ?> <em>*</em><?php endif; ?></span>
                        <input type="email" name="mail" id="mail" value="<?php $this->remember('mail'); ?>" placeholder="name@example.com"<?php if ($this->options->commentsRequireMail): ?> required<?php endif; ?>>
                    </label>
                    <label class="field">
                        <span class="field-label"><?php _e('网站'); ?><?php if ($this->options->commentsRequireUrl): ?> <em>*</em><?php endif; ?></span>
                        <input type="url" name="url" id="url" value="<?php $this->remember('url'); ?>" placeholder="https://"<?php if ($this->options->commentsRequireUrl): ?> required<?php endif; ?>>
                    </label>
                </div>
            <?php endif; ?>

            <div class="form-editor">
                <textarea name="text" id="textarea" rows="5" placeholder="<?php _e('我也说一句'); ?>" required><?php $this->remember('text'); ?></textarea>
                <div class="editor-tools">
                    <button type="button" class="tool-btn" id="emoji-btn" data-emoji-toggle aria-expanded="false">
                        <?php sleek_icon('smile'); ?><?php _e('表情'); ?>
                    </button>
                    <span class="tool-tip"><?php _e('支持 Markdown 语法'); ?></span>
                    <button type="submit" class="submit-btn" id="comment-submit">
                    <span class="submit-label"><?php _e('发送'); ?></span><?php sleek_icon('send'); ?>
                </button>
                </div>
                <div class="emoji-panel" id="emoji-panel" hidden>
                    <?php
                    $sleekEmojis = array(
                        '😀', '😄', '😁', '😆', '😅', '😂', '🤣', '😊', '😇', '🙂',
                        '🙃', '😉', '😌', '😍', '🥰', '😘', '😋', '😛', '🤪', '😜',
                        '🤨', '🧐', '🤓', '😎', '🥳', '😏', '😒', '😞', '😔', '😟',
                        '😕', '🙁', '😣', '😖', '😫', '😩', '🥺', '😢', '😭', '😤',
                        '😠', '😡', '🤯', '😳', '🥵', '🥶', '😱', '😨', '😰', '🤗',
                        '🤔', '🤭', '🤫', '🤥', '😶', '😐', '😑', '😬', '🙄', '😯',
                        '😴', '🤤', '😪', '😵', '🤐', '🥴', '🤢', '🤮', '🤧', '😷',
                        '🤒', '🤕', '👍', '👎', '👏', '🙌', '🙏', '🤝', '💪', '✌️',
                        '🤞', '👌', '👋', '🫡', '🎉', '🔥', '✨', '💡', '❤️', '💔',
                        '🌹', '🍀', '🌈', '☕', '🍉', '🎵', '📷', '🚀', '🌙', '⭐',
                    );
                    foreach ($sleekEmojis as $sleekEmoji) {
                        echo '<button type="button" class="emoji-item" data-emoji="' . $sleekEmoji . '">' . $sleekEmoji . '</button>';
                    }
                    ?>
                </div>
            </div>
        </form>
    </div>
<?php else: ?>
    <p class="comments-closed"><?php _e('评论已关闭。'); ?></p>
<?php endif; ?>
