import React, { useState } from 'react';

const QuizComponent = ({ quizData }) => {
    const [currentQuestionNumber, setCurrentQuestionNumber] = useState(0);
    const [answers, setAnswers] = useState({});

    const currentQuestion = quizData.questions[currentQuestionNumber]; // Ensure quizData has 'questions'

    const handleAnswerChange = (answer) => {
        setAnswers({
            ...answers,
            [currentQuestion.questionText]: answer
        });
    };

    const nextQuestion = () => {
        if (currentQuestionNumber < quizData.questions.length - 1) {
            setCurrentQuestionNumber(currentQuestionNumber + 1);
        }
    };

    const previousQuestion = () => {
        if (currentQuestionNumber > 0) {
            setCurrentQuestionNumber(currentQuestionNumber - 1);
        }
    };

    return (
        <div>
            <h2>{quizData.quizTitle}</h2>
            <div>
                <h3>{currentQuestion.questionText}</h3>
                <div>
                    <input
                        type="radio"
                        name={currentQuestion.questionText}
                        value={currentQuestion.answerA}
                        onChange={() => handleAnswerChange(currentQuestion.answerA)}
                        checked={answers[currentQuestion.questionText] === currentQuestion.answerA}
                    />
                    <label>{currentQuestion.answerA}</label>
                </div>
                <div>
                    <input
                        type="radio"
                        name={currentQuestion.questionText}
                        value={currentQuestion.answerB}
                        onChange={() => handleAnswerChange(currentQuestion.answerB)}
                        checked={answers[currentQuestion.questionText] === currentQuestion.answerB}
                    />
                    <label>{currentQuestion.answerB}</label>
                </div>
                <div>
                    <input
                        type="radio"
                        name={currentQuestion.questionText}
                        value={currentQuestion.answerC}
                        onChange={() => handleAnswerChange(currentQuestion.answerC)}
                        checked={answers[currentQuestion.questionText] === currentQuestion.answerC}
                    />
                    <label>{currentQuestion.answerC}</label>
                </div>
                <div>
                    <input
                        type="radio"
                        name={currentQuestion.questionText}
                        value={currentQuestion.answerD}
                        onChange={() => handleAnswerChange(currentQuestion.answerD)}
                        checked={answers[currentQuestion.questionText] === currentQuestion.answerD}
                    />
                    <label>{currentQuestion.answerD}</label>
                </div>
            </div>
            <div>
                <button onClick={previousQuestion} disabled={currentQuestionNumber === 0}>Previous</button>
                <button onClick={nextQuestion} disabled={currentQuestionNumber === quizData.questions.length - 1}>Next</button>
            </div>

            {/* Collect and submit answers in a form */}
            <form action="quizResults.php" method="POST">
                <input type="hidden" name="episodeID" value={quizData.episodeID} /> {/* Use episodeID instead of quizTitle */}
                {Object.keys(answers).map((question, index) => (
                    <input 
                        key={index}
                        type="hidden"
                        name={`answer[${question}]`}
                        value={answers[question]}
                    />
                ))}
                <button type="submit">Submit my answers</button>
            </form>
        </div>
    );
};

export default QuizComponent;
