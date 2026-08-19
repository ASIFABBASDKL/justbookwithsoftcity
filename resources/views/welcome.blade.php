<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>JustBook</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      height: 100vh;
      overflow: hidden;
      display: flex;
      justify-content: center;
      align-items: center;
      background: linear-gradient(180deg, #fdfdfd, #e6f0ff); /* White to light blue */
      font-family: 'Poppins', sans-serif;
    }

    h1 {
      position: absolute;
      top: 15%;
      font-size: 100px;
      font-weight: 900;
      text-transform: uppercase;
      letter-spacing: 8px;
      background: linear-gradient(90deg, #333, #666);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      animation: floatText 3s ease-in-out infinite;
      z-index: 10;
    }

    @keyframes floatText {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-15px); }
    }

    /* Ocean container */
    .ocean {
      position: absolute;
      bottom: 0;
      width: 100%;
      height: 200px;
      overflow: hidden;
      z-index: 1;
      background: transparent;
    }

    /* Wave using SVG */
    .wave {
      position: absolute;
      width: 200%;
      height: 100%;
      background: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 120 28'><path fill='rgba(0,153,255,0.5)' d='M0 17 Q 30 7 60 17 T 120 17 V30 H0 Z'></path></svg>") repeat-x;
      background-size: 120px 100%;
      animation: wave 8s linear infinite;
    }

    .wave:nth-child(2) {
      bottom: 10px;
      opacity: 0.5;
      animation: wave 12s linear infinite reverse;
    }

    .wave:nth-child(3) {
      bottom: 20px;
      opacity: 0.3;
      animation: wave 18s linear infinite;
    }

    @keyframes wave {
      0% { transform: translateX(0); }
      100% { transform: translateX(-50%); }
    }
  </style>
</head>
<body>
  <h1>JUSTBOOK</h1>

  <div class="ocean">
    <div class="wave"></div>
    <div class="wave"></div>
    <div class="wave"></div>
  </div>
</body>
</html>
