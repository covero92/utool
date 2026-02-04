import sys

try:
    with open('src/context/PPRContext.tsx', 'r', encoding='utf-8') as f:
        text = f.read()
except Exception as e:
    print(f"Error reading file: {e}")
    sys.exit(1)

balance = 0
stack = []

for i, char in enumerate(text):
    if char == '{':
        balance += 1
        stack.append(i)
    elif char == '}':
        balance -= 1
        if stack:
            stack.pop()
        else:
            line_num = text[:i].count('\n') + 1
            print(f"Extra closing brace at char {i}, line {line_num}")
            # print context
            start = max(0, i-50)
            end = min(len(text), i+50)
            print(f"Context: ...{text[start:end]}...")
            sys.exit(1)

if balance > 0:
    last_open = stack[-1]
    line_num = text[:last_open].count('\n') + 1
    print(f"Unclosed open braces: {balance}")
    print(f"Last open brace at char {last_open}, line {line_num}")
    sys.exit(1)
elif balance == 0:
    print("Braces balanced")
