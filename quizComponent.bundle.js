import React from 'react';
import ReactDOM from 'react-dom/client';
import QuizComponent from './QuizComponent';  // Import your component

// Get the quiz data from a global variable set in the HTML
const quizData = window.quizData;



const rootElement = document.getElementById('quiz-root');
if (rootElement) {
    ReactDOM.render(<QuizComponent quizData={quizData} />, rootElement); // Use render instead of createRoot
} else {
    console.error("No element with id 'quiz-root' found.");
}

