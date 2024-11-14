<div id="content">
    <div class="columns">
        <!-- Adjusted the left column to be narrower -->
        <div class="column is-one-quarter">
            <div class="box">
                <div id="storyText">
                    <p><?php echo htmlspecialchars($currentStory['storyText']); ?></p>
                </div>
            </div>
        </div>
        <div class="column is-two-thirds">
            <div class="box">
                <form id="quizForm" action="next_question.php" method="POST">
                    <div class="field" id="questionField">
                        <label class="label"><?php echo htmlspecialchars($currentStory['storyQuestion']); ?></label>
                        <div class="control">
                            <label class="radio">
                                <input type="radio" name="answer" value="A" required> <?php echo htmlspecialchars($currentStory['answerA']); ?>
                            </label>
                        </div>
                        <div class="control">
                            <label class="radio">
                                <input type="radio" name="answer" value="B" required> <?php echo htmlspecialchars($currentStory['answerB']); ?>
                            </label>
                        </div>
                        <div class="control">
                            <label class="radio">
                                <input type="radio" name="answer" value="C" required> <?php echo htmlspecialchars($currentStory['answerC']); ?>
                            </label>
                        </div>
                    </div>
                    <input type="hidden" name="episodeID" value="<?php echo $episodeID; ?>">
                    <button class="button is-primary" type="submit">Submit Answer</button>
                </form>
            </div>
        </div>
    </div>
    <div id="feedback" class="notification"></div>
</div>

<script>
    $(document).ready(function() {
        $('#quizForm').on('submit', function(e) {
            e.preventDefault(); // Prevent the default form submission

            $.ajax({
                url: 'next_question.php', // The PHP endpoint
                type: 'POST',
                data: $(this).serialize(), // Send form data
                dataType: 'json', // Expect JSON response
                success: function(response) {
                    // Show completion message if quiz is completed
                    if (response.completed) {
                        $('#content').html('<div class="notification is-success">' + response.message + '</div>');
                        return;
                    }

                    // If the answer was correct, load the next question
                    if (response.correct) {
                        $('#storyText').html('<p>' + response.storyText + '</p>');
                        $('#questionField').html(
                            '<label class="label">' + response.storyQuestion + '</label>' +
                            '<div class="control"><label class="radio"><input type="radio" name="answer" value="A" required> ' + response.answerA + '</label></div>' +
                            '<div class="control"><label class="radio"><input type="radio" name="answer" value="B" required> ' + response.answerB + '</label></div>' +
                            '<div class="control"><label class="radio"><input type="radio" name="answer" value="C" required> ' + response.answerC + '</label></div>'
                        );
                        $('#feedback').removeClass('is-danger').addClass('is-success').text(response.message);
                    } else {
                        // Show incorrect feedback without advancing
                        $('#feedback').removeClass('is-success').addClass('is-danger').text(response.message);
                    }
                }
            });
        });
    });
</script>

<?php
echo makeFooter("This is the footer");
echo makePageEnd();
?>
