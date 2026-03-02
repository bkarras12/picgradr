from langchain_openai import ChatOpenAI
from langchain_core.messages import HumanMessage
from pydantic import BaseModel, Field
from typing import Literal

Grade = Literal["A", "B", "C", "D", "F"]


class PhotoGrades(BaseModel):
    lighting: Grade
    lighting_feedback: str = Field(description="1-2 sentence feedback on lighting quality")
    composition: Grade
    composition_feedback: str = Field(description="1-2 sentence feedback on framing and composition")
    clarity: Grade
    clarity_feedback: str = Field(description="1-2 sentence feedback on sharpness and image quality")
    background: Grade
    background_feedback: str = Field(description="1-2 sentence feedback on the background")
    expression: Grade
    expression_feedback: str = Field(description="1-2 sentence feedback on expression and approachability")
    overall: Grade
    summary: str = Field(description="2-3 sentence overall summary and top recommendation")


SYSTEM_PROMPT = """\
You are an expert dating profile photo evaluator. A user has uploaded a photo they want to use on a dating app.

Grade the photo across 5 metrics using A–F letter grades:
  A = Excellent
  B = Good
  C = Average / could be improved
  D = Poor
  F = Unacceptable

Metrics to evaluate:
1. Lighting — Is the subject well-lit? Natural or flattering studio light? Are there harsh shadows or overexposure?
2. Composition — Is the subject well-framed and centered? Is the crop flattering? Does it follow good photo composition?
3. Clarity — Is the image sharp and in focus? Is the resolution high enough? Any motion blur or pixelation?
4. Background — Is the background clean and non-distracting? Is it appropriate for a dating profile?
5. Expression — Does the subject look approachable, confident, and genuine? Is there a natural smile?

Be honest but constructive. Give specific, actionable feedback for each metric.
For the overall grade, consider the photo holistically as a dating profile image.
"""


def grade_image(base64_data: str, media_type: str) -> PhotoGrades:
    model = ChatOpenAI(model="gpt-4o-mini")
    structured = model.with_structured_output(PhotoGrades)

    msg = HumanMessage(content=[
        {
            "type": "image_url",
            "image_url": {
                "url": f"data:{media_type};base64,{base64_data}",
            },
        },
        {
            "type": "text",
            "text": SYSTEM_PROMPT + "\n\nPlease grade this dating profile photo.",
        },
    ])

    return structured.invoke([msg])
