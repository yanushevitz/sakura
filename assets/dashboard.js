import './styles/dashboard.css';
import MicroModal from 'micromodal';

function shrinkComments() {
  let comments = document.querySelectorAll(".comment .text")
  comments.forEach(function (comment) {
    if (comment.textContent.length > 180) {
      comment.textContent = comment.textContent.substring(0, 180) + ".. [...]"
    }
  })
}


async function fetchPosts() {
  // let loading = document.createElement('div')
  // let image = document.createElement("img")
  // image.setAttribute("src", "https://sezeromer.com/wp-content/uploads/2019/09/Infinity-1s-200px.gif")
  // loading.append(image)
  // document.querySelector(".container").append(loading)
  let posty = await fetch("http://judasz.ddns.net:8000/async/posts").then(response => response.json())
  posty.forEach(function (a) {
    let post = document.createElement('div')
    let text = document.createElement('div')
    let image = document.createElement('div')
    let menu = document.createElement('div')
    let comments = document.createElement('div')

    post.setAttribute('class', 'post')
    text.setAttribute('class', 'text')
    image.setAttribute('class', 'image')
    menu.setAttribute('class', 'menu')
    comments.setAttribute('class', 'comments')

    text.innerText = a.text
    image.innerHTML = "<img src='http://judasz.ddns.net:8000/images/" + a.image + "'>"
    menu.innerHTML = "<div class='post-menu'><span class='reactions'>reactions: " + a.reactions + "</span><a href=''>add comment</a><a href=''>report</a><a href=''>follow thread</a></div>"

    a.comments.forEach(function (a) {
      let comment = document.createElement("div")
      let author = document.createElement('div')
      let text = document.createElement('div')


      comment.setAttribute("class", "comment")
      author.setAttribute("class", "author")
      text.setAttribute("class", "text")

      author.innerText = "Author"
      text.innerText = a.text

      comment.append(author)
      comment.append(text)

      comments.append(comment)

    })

    post.append(text)
    post.append(image)
    post.append(menu)
    post.append(comments)
    // document.querySelector('.container').remove(image)
    document.querySelector(".container").append(post)
  })
}

function postModal() {


  // Get the modal
  var modal = document.getElementById("myModal");

  var span = document.getElementsByClassName("close")[0];
  let vutn = document.querySelector("#myBtn");

  vutn.onclick = function () {
    modal.style.display = "block";
  }

  span.onclick = function () {
    modal.style.display = "none";
  }

  window.onclick = function (event) {
    if (event.target == modal) {
      modal.style.display = "none";
    }
  }

}

function createPost(){
  
  // let button = form.
  let button = document.querySelector("#submitPost")
  let form = document.querySelector("#postForm")  
  button.addEventListener("click", function(object){
    sendPost(form)
  })
  // form.addEventListener("")
}

async function sendPost(formData){
  let text = formData.querySelector("textarea")
  let file = formData.querySelector("input")
  let post = await fetch("async/post",{
    method: 'POST',
    body: JSON.stringify({
      text: text.value,
      file: file.value
    })
  })
  

}

fetchPosts()
shrinkComments()
postModal()
createPost()